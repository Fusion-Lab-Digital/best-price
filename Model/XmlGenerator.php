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

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Currency;
use Magento\Store\Model\StoreManagerInterface;

class XmlGenerator
{

    private ConfigProvider $configProvider;

    private CollectionFactory $collectionFactory;

    private StoreManagerInterface $storeManager;

    private State $state;

    private Currency $currency;

    private ?\XMLWriter $xmlWriter;

    private string $productIdentifier;

    /**
     * @param ConfigProvider $configProvider
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param State $state
     * @param Currency $currency
     */
    public function __construct(
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        State $state,
        Currency $currency
    ) {
        $this->configProvider = $configProvider;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->currency = $currency;
        $this->xmlWriter = new \XMLWriter();
        $this->productIdentifier = $this->configProvider->getProductIdentifier();
    }

    public function generate()
    {
        try {
            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }


        $this->xmlWriter->openMemory();
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->writeElement('store', $this->storeManager->getStore()->getName());

        $productCollection = $this->getCollection();

        $totalItems = $productCollection->getSize();
        $totalPages = ceil($totalItems / $this->configProvider->getXMLBatchSize());


        for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++) {
            $productCollection->setCurPage($currentPage);

            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($productCollection as $product) {
                $this->xmlWriter->startElement('products');
                $this->insertProduct($product);
                $this->xmlWriter->endElement();
            }

            dd($this->xmlWriter->outputMemory());


            $productCollection->clear();
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    private function insertProduct(\Magento\Catalog\Model\Product $product):void
    {
        $this->xmlWriter->startElement('product');

        $this->xmlWriter->writeElement('uuid', $this->getProductIdentifier($product));
        $this->xmlWriter->writeElement('title', $product->getName());
        $this->xmlWriter->writeElement('productURL', $product->getProductUrl());
        $this->insertProductImages($product);
        $this->xmlWriter->writeElement('price', $product->getFinalPrice() . ' ' . $this->storeManager->getStore()->getCurrentCurrency()->getCode());


        $this->xmlWriter->endElement();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    private function insertProductImages(\Magento\Catalog\Model\Product $product):void
    {
        $images = $product->getMediaGalleryImages();
        $this->xmlWriter->startElement('imagesURL');
        $counter = 1;
        foreach ($images as $image) {
            $this->xmlWriter->writeElement('img'. $counter, $image->getUrl());
            $counter++;
        }

        $this->xmlWriter->endElement();
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCollection():Collection
    {
        return $this->collectionFactory->create()
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setPageSize($this->configProvider->getXMLBatchSize())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('small_image', ['neq' => 'no_selection'])
            ->addMediaGalleryData()
            ->addFinalPrice();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    private function getProductIdentifier(\Magento\Catalog\Model\Product $product):string
    {
        if ($this->productIdentifier === 'sku') {
            return $product->getSku();
        }
        return (string) $product->getProductId();
    }
}



// <product>
// <uid>{{product.id}}</uid>
// <name>{{product.clean_name}}</name>
// <link>{{product.xml_product_url}}</link>
// <image>{{product.image_link index=0 | parent.image_link index=0}}</image>
// <!-- Additional Images -->
// <additional_image_link>{{parent.image_link index="1" | product.image_link index="1"}}</additional_image_link>
// <additional_image_link>{{parent.image_link index="2" | product.image_link index="2"}}</additional_image_link>
// <additional_image_link>{{parent.image_link index="3" | product.image_link index="3"}}</additional_image_link>
// <additional_image_link>{{parent.image_link index="4" | product.image_link index="4"}}</additional_image_link>
// <additional_image_link>{{parent.image_link index="5" | product.image_link index="5"}}</additional_image_link>
// <additional_image_link>{{parent.image_link index="6" | product.image_link index="6"}}</additional_image_link>
// <additional_image_link>{{parent.image_link index="7" | product.image_link index="7"}}</additional_image_link>
// <description>{{product.description}}</description>
// <!-- <price>{{product.min_price | product.sale_price | product.final_price}}</price>-->
// <price>{{product.min_price suffix=" EUR" | product.sale_price | product.final_price suffix=" EUR"}}</price>
// <category>{{product.categories nth="-1" from="3" | parent.categories nth="-1" from="3" }}</category>
// <manufacturer>{{product.manufacturer}}}</manufacturer>
// <availability>Διαθέσιμο απο 4 εώς 10 ημέρες></availability>
// <instock>Y</instock>
// <sku>{{product.sku}}</sku>
// <mpn>{{product.sku}}</mpn>
// <ean>{{product.ean}}</ean>
// <size>{{product.fallback_size}}</size>
// <color>{{product.color}}</color>
// <quantity>{{product.qty}}</quantity>
// </product>
