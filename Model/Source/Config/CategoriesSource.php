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

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

class CategoriesSource implements OptionSourceInterface
{

    private CollectionFactory $categoryCollectionFactory;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function toOptionArray():array
    {
        $result = $this->getSortedCategoryTree();
        usort(
            $result,
            function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            }
        );
        return $result;
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSortedCategoryTree():array
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'parent_id', 'is_active'])
            ->addAttributeToFilter('is_active', 1);

        $categories = [];
        foreach ($collection as $category) {
            $categories[$category->getId()] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'parent_id' => $category->getParentId(),
            ];
        }


        return $this->buildCategoryTree($categories);
    }

    /**
     * @param array $categories
     * @param int $parentId
     * @param string $prefix
     * @return array
     */
    private function buildCategoryTree(array $categories, int $parentId = 1, string $prefix = ''): array
    {
        $tree = [];
        foreach ($categories as $category) {
            if ((int) $category['parent_id'] === $parentId) {
                $label = $prefix ? $prefix . ' -> ' . $category['name'] : $category['name'];
                $tree[] = [
                    'label' => $label,
                    'value' => $category['id'],
                ];

                $tree = array_merge(
                    $tree,
                    $this->buildCategoryTree($categories, $category['id'], $label)
                );
            }
        }
        return $tree;
    }
}
