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

namespace FusionLab\BestPrice\Model\Source\Config;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class ProductSelectAttributes implements OptionSourceInterface
{

    private AdapterInterface $connection;

    /**
     * @param ResourceConnection $connection
     */
    public function __construct(ResourceConnection $connection)
    {
        $this->connection = $connection->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->getAttributes() as $attribute) {
            $options[] = [
                'label' => "[Code: " . $attribute['attribute_code'] . "] " . $attribute['frontend_label'],
                'value' => $attribute['attribute_code'],
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    private function getAttributes():array
    {
        $select = $this->connection->select()
            ->from(['eav' => $this->connection->getTableName('eav_attribute')], ['attribute_code', 'frontend_label'])
            ->joinLeft(
                ['eav_type' => $this->connection->getTableName('eav_entity_type')],
                'eav_type.entity_type_id = eav.entity_type_id',
                []
            )
            ->where('eav_type.entity_type_code = ?', 'catalog_product')
            ->where('eav.backend_type = ?', 'int')
            ->where('eav.frontend_input = ?', 'select');

        return $this->connection->fetchAll($select);
    }
}
