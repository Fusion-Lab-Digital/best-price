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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UrlProcessor
{

    private ?array $configurableData = null;

    private ProductRepositoryInterface $productRepository;

    private AdapterInterface $connection;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resourceConnection
    ) {
        $this->productRepository = $productRepository;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function getUrl(\Magento\Catalog\Api\Data\ProductInterface $product): string
    {
        $this->_initData();

        if (!isset($this->configurableData[$product->getId()])){
            return $product->getProductUrl();
        }

        $config = $this->configurableData[$product->getId()];

        $params = [];
        foreach ($config['attributes'] as $attributeCode){
            $value = $product->getData($attributeCode);
            if ($value){
                $params[] = "{$attributeCode}={$value}";
            }
        }

        $configurableProduct = $this->productRepository->getById($config['parent_id']);
        $suffix = !empty($params) ? '#' . implode('&', $params) : '';

        return $configurableProduct->getProductUrl() . $suffix;
    }

    /**
     * Initialize data
     * @return void
     */
    private function _initData(): void {
        if (!$this->configurableData){
            $this->configurableData = [];
            $rawData = $this->_getRawData();


            foreach ($rawData as $rawDatum) {
                $configurableData = [
                    'parent_id' => $rawDatum['configurable_id'],
                    'attributes' => explode(',', $rawDatum['attributes'])
                ];

                $simpleIds = explode(',', $rawDatum['simple_ids']);

                foreach ($simpleIds as $simpleId){
                    $this->configurableData[$simpleId] = $configurableData;
                }
            }

        }
    }

    /**
     * Get raw data from select
     * @return array
     */
    private function _getRawData(): array {
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
            ->columns([
                'attributes' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT eav.attribute_code)'),
                'simple_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT cpsl.product_id)'),
            ]);


        return $this->connection->fetchAll($select);
    }
}
