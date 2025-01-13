<?php
/**
 * Copyright (c) 2025 Fusion Lab G.P
 * Website: https://fusionlab.gr
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace FusionLab\BestPrice\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class XmlGenerator
{

    const XML_WRITE_DIR = 'fusionlab_feeds';

    private ConfigProvider $configProvider;

    private CollectionFactory $collectionFactory;

    private CategoryRepositoryInterface $categoryRepository;

    private StoreManagerInterface $storeManager;

    private State $state;

    private SourceItemRepositoryInterface $sourceItemRepository;

    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private AdapterInterface $connection;

    private File $file;

    private Filesystem $filesystem;

    private UrlProcessor $urlProcessor;

    private ?\XMLWriter $xmlWriter;

    private string $productIdentifier;

    private ?string $currentWebsiteCode;

    private array $inventorySourceCodes = [];

    private array $inventoryStockIds = [];

    private bool $availabilityFromProduct;

    private bool $manufacturerFromProduct;

    private array $productQuantities = [];

    private array $loadedCategories = [];

    /**
     * @param ConfigProvider $configProvider
     * @param CollectionFactory $collectionFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param State $state
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResourceConnection $connection
     * @param File $file
     * @param Filesystem $filesystem
     */
    public function __construct(
        ConfigProvider                $configProvider,
        CollectionFactory             $collectionFactory,
        CategoryRepositoryInterface   $categoryRepository,
        StoreManagerInterface         $storeManager,
        State                         $state,
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder         $searchCriteriaBuilder,
        ResourceConnection            $connection,
        File                          $file,
        Filesystem                    $filesystem,
        UrlProcessor $urlProcessor,
    ) {
        $this->configProvider = $configProvider;
        $this->collectionFactory = $collectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->connection = $connection->getConnection();
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->productIdentifier = $this->configProvider->getProductIdentifier();
        $this->availabilityFromProduct = $this->configProvider->getIsAvailabilityFromProductAttribute();
        $this->manufacturerFromProduct = $this->configProvider->getIsBrandFromProductAttribute();
        $this->urlProcessor = $urlProcessor;
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function initGenerateXml(): void
    {
        if (!$this->configProvider->getIsXMLExportEnabled()) {
            return;
        }

        try {
            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }

        foreach ($this->configProvider->getStoreViewsToExport() as $storeViewId) {
            $this->storeManager->setCurrentStore($storeViewId);
            $this->currentWebsiteCode = $this->storeManager->getWebsite()->getCode();
            $this->generate();
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function generate(): void
    {
        $this->xmlWriter = new \XMLWriter();
        $this->xmlWriter->openMemory();
        $this->xmlWriter->setIndent(true);
        $this->xmlWriter->setIndentString('    ');
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->startElement('store');

        $productCollection = $this->getCollection();
        $totalItems = $productCollection->getSize();
        $totalPages = ceil($totalItems / $this->configProvider->getXMLBatchSize());
        $this->xmlWriter->startElement('products');

        for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++) {
            $this->productQuantities = [];
            $productCollection->setCurPage($currentPage);
            $this->prepareProductQuantities($productCollection);
            /** @var Product $product */
            foreach ($productCollection as $product) {
                $this->insertProduct($product);
            }
            $productCollection->clear();
        }
        $this->xmlWriter->endElement(); // End element products.
        $this->xmlWriter->endElement(); // End element store.

        $this->saveXml($this->xmlWriter->outputMemory());
        $this->xmlWriter->flush();
    }

    /**
     * @param Product $product
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function insertProduct(Product $product): void
    {

        if (!$this->configProvider->shouldIncludeOutOfStockProducts()
            && (!isset($this->productQuantities[$product->getSku()]) || $this->productQuantities[$product->getSku()] === 0.0)
        ) {
            return;
        }

        $this->xmlWriter->startElement('product');

        $this->insertRow('uuid', $this->getProductIdentifier($product));
        $this->insertRow('title', html_entity_decode($product->getName()));
        $this->insertRow('productURL', $this->urlProcessor->getUrl($product));
        $this->insertRow('price', $product->getFinalPrice() . ' ' . $this->storeManager->getStore()->getCurrentCurrency()->getCode());
        $this->insertRow('category_name', html_entity_decode($this->getCategoriesNames($product)));
        $this->insertRow('manufacturer', $this->getProductBrandValue($product));
        $this->insertRow('availability', $this->getProductAvailabilityValue($product));
        $this->insertRow('stock', (isset($this->productQuantities[$product->getSku()]) && $this->productQuantities[$product->getSku()] > 0 ) ? 'Y' : 'N');
        $this->insertRow('sku', $product->getSku());
        $this->insertRow('mpn', $this->configProvider->getMpnProductAttribute() === 'sku' ? $product->getSku() : $product->getAttributeText($this->configProvider->getMpnProductAttribute()));
        $this->insertRow('weight', $product->getWeight());
        if ($size = $product->getAttributeText($this->configProvider->getSizeProductAttribute())) {
            $this->insertRow('size', $size);
        }
        if ($color = $product->getAttributeText($this->configProvider->getColorProductAttribute())) {
            $this->insertRow('color', $color);
        }
        $this->insertRow('quantity', isset($this->productQuantities[$product->getSku()]) ? $this->productQuantities[$product->getSku()] : 0);
        $this->insertProductImages($product);

        $this->xmlWriter->endElement();
    }

    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    private function insertRow(string $name, string $value):void
    {
        $this->xmlWriter->startElement($name);
        $this->xmlWriter->writeCdata($value);
        $this->xmlWriter->endElement();
    }

    /**
     * @param string $xml
     * @return void
     * @throws FileSystemException
     */
    private function saveXml(string $xml)
    {
        $xmlDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath(self::XML_WRITE_DIR);

        if ($this->filesystem->getDirectoryWrite(DirectoryList::PUB)->getAbsolutePath($xmlDirectory)) {
            $this->file->mkdir($xmlDirectory, 0755);
        }
        $websiteCode = preg_replace('/[^a-z0-9]+/i', '_', $this->currentWebsiteCode);
        $websiteCode = preg_replace('/([a-z])([A-Z])/', '$1_$2', $websiteCode);
        $websiteCode = strtolower($websiteCode);

        $completePath = $xmlDirectory . DIRECTORY_SEPARATOR . $websiteCode . DIRECTORY_SEPARATOR . $this->storeManager->getStore()->getCode() . '_bestprice.xml';
        $this->file->write($completePath, $xml);
    }

    /**
     * @param Collection $collection
     * @return void
     */
    private function prepareProductQuantities(Collection $collection): void
    {
        $skus = [];
        foreach ($collection as $product) {
            $skus[] = $product->getSku();
        }
        $this->productQuantities = $this->getProductsQuantity($skus);
    }

    /**
     * @param Product $product
     * @return void
     */
    private function insertProductImages(Product $product): void
    {
        $images = $product->getMediaGalleryImages();
        $this->xmlWriter->startElement('imagesURL');
        $counter = 1;
        foreach ($images as $image) {
            $this->insertRow('img' . $counter, $image->getUrl());
            $counter++;
        }

        $this->xmlWriter->endElement();
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCollection(): Collection
    {
        $collection = $this->collectionFactory->create()
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setPageSize($this->configProvider->getXMLBatchSize())
            ->addAttributeToSelect($this->getProductCollectionSelectAttributes())
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('small_image', ['neq' => 'no_selection'])
            ->addCategoriesFilter(['nin' => $this->configProvider->getExcludedCategoryIds()]);

        if ($this->configProvider->shouldIncludeOutOfStockProducts()) {
            $collection->setFlag('has_stock_status_filter', false);
        }
        if ($this->configProvider->shouldIncludeWithAttributes()) {
            $collection->addAttributeToFilter($this->configProvider->getProductFlagAttribute(), true);
        }

        $collection->addMediaGalleryData();
        return $collection;
    }

    /**
     * @return array
     */
    private function getProductCollectionSelectAttributes(): array
    {
        $attributes = [
            'entity_id',
            'sku',
            'name',
            'category_ids',
            'status',
            'price',
            'special_price',
            'weight',
        ];

        if ($this->availabilityFromProduct) {
            $attributes[] = $this->configProvider->getProductAvailabilityAttribute();
        }
        if ($this->manufacturerFromProduct) {
            $attributes[] = $this->configProvider->getProductBrandAttribute();
        }

        $select = $this->connection->select()
            ->from(
                ['cpsl' => $this->connection->getTableName('catalog_product_super_link')],
                ['eav.attribute_code']
            )
            ->join(
                ['cpsa' => $this->connection->getTableName('catalog_product_super_attribute')],
                'cpsl.parent_id = cpsa.product_id',
                []
            )
            ->join(
                ['eav' => $this->connection->getTableName('eav_attribute')],
                'cpsa.attribute_id = eav.attribute_id',
                []
            )
            ->group('eav.attribute_code');

        return array_merge($attributes, $this->connection->fetchCol($select));
    }

    /**
     * @param Product $product
     * @return string
     */
    private function getProductIdentifier(Product $product): string
    {
        if ($this->productIdentifier === 'sku') {
            return $product->getSku();
        }
        return (string) $product->getProductId();
    }

    /**
     * @param Product $product
     * @return string
     * @throws NoSuchEntityException
     */
    private function getCategoriesNames(Product $product): string
    {
        $categories = [];
        $categoryIds = $product->getCategoryIds();
        if (!$categoryIds) {
            return '';
        }
        $select = $this->connection->select()
            ->from($this->connection->getTableName('catalog_category_entity'), ['path'])
            ->where('entity_id in (?)', $categoryIds)
            ->order('LENGTH(path) DESC')
            ->limit(1);

        $deepestPath = explode('/', $this->connection->fetchOne($select));

        foreach ($deepestPath as $id) {
            if (!isset($this->loadedCategories[$id])) {
                $this->loadedCategories[$id] = $this->categoryRepository->get($id);
            }
            $category = $this->loadedCategories[$id];
            if ($category->getLevel() <= 1) {
                continue;
            }
            $categories[] = $category->getName();
        }

        foreach ($product->getCategoryCollection() as $category) {
            $categories[] = $category->getName();
        }

        return implode(' -> ', array_filter($categories));
    }

    /**
     * @param Product $product
     * @return string
     */
    private function getProductAvailabilityValue(Product $product): string
    {
        if ($this->availabilityFromProduct) {
            return $product->getAttributeText($this->configProvider->getProductAvailabilityAttribute());
        }
        return $this->configProvider->getFixedAvailabilityValue();
    }

    /**
     * @param Product $product
     * @return string
     */
    private function getProductBrandValue(Product $product): string
    {
        if ($this->manufacturerFromProduct) {
            return $product->getAttributeText($this->configProvider->getProductBrandAttribute());
        }
        return $this->configProvider->getFixedBrandValue();
    }

    /**
     * @param array $skus
     * @return array
     */
    private function getProductsQuantity(array $skus): array
    {
        $quantities = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $skus, 'IN')
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $sourceCodesToSumQuantity = $this->getSourceCodesAssignedPerWebsiteCode();
        foreach ($sourceItems as $sourceItem) {
            $quantities[$sourceItem->getSku()] = 0;
            if (in_array($sourceItem->getSourceCode(), $sourceCodesToSumQuantity)) {
                $quantities[$sourceItem->getSku()] = (float) $sourceItem->getQuantity();
            }
        }
        return $quantities;
    }

    /**
     * @return array
     */
    private function getSourceCodesAssignedPerWebsiteCode(): array
    {
        if (!isset($this->inventorySourceCodes[$this->currentWebsiteCode])) {
            $select = $this->connection->select()
                ->from($this->connection->getTableName('inventory_source_stock_link'), ['source_code'])
                ->where('stock_id = ?', $this->getInventoryStockId());

            $this->inventorySourceCodes[$this->currentWebsiteCode] = $this->connection->fetchCol($select);
        }
        return $this->inventorySourceCodes[$this->currentWebsiteCode];
    }

    /**
     * @return int
     */
    private function getInventoryStockId(): int
    {
        if (!isset($this->inventoryStockIds[$this->currentWebsiteCode])) {
            $select = $this->connection->select()
                ->from($this->connection->getTableName('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $this->currentWebsiteCode);

            $this->inventoryStockIds[$this->currentWebsiteCode] = (int) $this->connection->fetchOne($select);
        }
        return $this->inventoryStockIds[$this->currentWebsiteCode];
    }
}
