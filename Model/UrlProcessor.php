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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class UrlProcessor
{

    private ?array $configurableData = null;

    private ProductRepositoryInterface $productRepository;

    private AdapterInterface $connection;

    private LoggerInterface $logger;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection         $resourceConnection,
        LoggerInterface            $logger
    ) {
        $this->productRepository = $productRepository;
        $this->connection = $resourceConnection->getConnection();
        $this->logger = $logger;
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    public function getUrl(ProductInterface $product): string
    {
        $this->initProductAttributesData();

        if (!isset($this->configurableData[$product->getId()])) {
            return $product->getProductUrl();
        }

        $config = $this->configurableData[$product->getId()];

        $params = [];
        foreach ($config['attributes'] as $attributeCode) {
            $value = $product->getData($attributeCode);
            if ($value) {
                $params[] = "{$attributeCode}={$value}";
            }
        }

        try {
            $configurableProduct = $this->productRepository->getById($config['parent_id']);
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e, $e->getTrace());
            return $product->getProductUrl();
        }

        $suffix = !empty($params) ? '#' . implode('&', $params) : '';
        return $configurableProduct->getProductUrl() . $suffix;
    }

    /**
     * @return void
     */
    private function initProductAttributesData(): void
    {
        if (!$this->configurableData) {
            $this->configurableData = [];
            $rawData = $this->getSimpleAttributesPairs();

            foreach ($rawData as $rawDatum) {
                $configurableData = [
                    'attributes' => explode(',', $rawDatum['attributes']),
                    'parent_id' => $rawDatum['configurable_id']
                ];

                $simpleIds = explode(',', $rawDatum['simple_ids']);

                foreach ($simpleIds as $simpleId) {
                    $this->configurableData[$simpleId] = $configurableData;
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getSimpleAttributesPairs(): array
    {
        $select = $this->connection->select()
            ->from(
                ['cpsl' => $this->connection->getTableName('catalog_product_super_link')],
                ['configurable_id' => 'parent_id']
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
            ->group('cpsl.parent_id')
            ->columns(
                [
                    'attributes' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT eav.attribute_code)'),
                    'simple_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT cpsl.product_id)'),
                ]
            );

        return $this->connection->fetchAll($select);
    }
}
