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

namespace FusionLab\BestPrice\Block;

use FusionLab\BestPrice\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Success extends \Magento\Framework\View\Element\Template
{

    protected $_template = 'FusionLab_BestPrice::success.phtml';

    private CollectionFactory $salesOrderCollection;

    private ConfigProvider $configProvider;

    private string $productIdentifier;

    /**
     * @param CollectionFactory $salesOrderCollection
     * @param ConfigProvider $configProvider
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        CollectionFactory $salesOrderCollection,
        ConfigProvider                                             $configProvider,
        Template\Context                                           $context,
        array                                                      $data = []
    ) {
        $this->salesOrderCollection = $salesOrderCollection;
        $this->configProvider = $configProvider;
        $this->productIdentifier = $this->configProvider->getProductIdentifier();
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getOrdersTrackingData(): array
    {
        $result = [];
        $orderIds = $this->getOrderIds(); // Set from observer.
        if (empty($orderIds) || !is_array($orderIds)) {
            return $result;
        }

        $collection = $this->salesOrderCollection->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);


        /** @var OrderInterface $order */
        foreach ($collection as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $result['products'][] = [
                    'orderId' => $order->getIncrementId(),
                    'price' => $item->getPrice(),
                    'productId' => $this->getProductIdentifier($item),
                    'quantity' => (float) $item->getQtyOrdered(),
                    'tittle' => addslashes($item->getName()),
                ];
            }
            $result['orders'][] = [
                'orderId' => $order->getIncrementId(),
                'revenue' => $order->getBaseGrandTotal(),
                'shipping' => $order->getShippingAmount(),
                'tax' => $order->getTaxAmount(),
            ];
        }
        return $result;
    }

    /**
     * @param OrderItemInterface $item
     * @return string
     */
    private function getProductIdentifier(OrderItemInterface $item):string
    {
        if ($this->productIdentifier === 'sku') {
            return $item->getSku();
        }
        return (string) $item->getProductId();
    }
}
