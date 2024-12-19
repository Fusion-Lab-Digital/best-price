<?php
/**
 * @copyright Copyright (c) 2024 Fusion Lab G.P
 * @package FusionLab_BestPrice
 */

namespace FusionLab\BestPrice\Block;

use FusionLab\BestPrice\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class Success extends \Magento\Framework\View\Element\Template
{

    protected $_template = 'FusionLab_BestPrice::success.phtml';

    private \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection;

    private ConfigProvider $configProvider;

    private string $productIdentifier;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection
     * @param ConfigProvider $configProvider
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        ConfigProvider                                             $configProvider,
        Template\Context                                           $context,
        array                                                      $data = []
    )
    {
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
        $orderIds = $this->getOrderIds(); //set from observer
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
                    'productId' => $this->getProductIdentifier($item),
                    'tittle' => addslashes($item->getName()),
                    'price' => $item->getPrice(),
                    'quantity' => (float)$item->getQtyOrdered(),
                ];
            }
            $result['orders'][] = [
                'orderId' => $order->getIncrementId(),
                'revenue' => $order->getBaseGrandTotal(),
                'tax' => $order->getTaxAmount(),
                'shipping' => $order->getShippingAmount(),
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
        if($this->productIdentifier === 'sku'){
            return $item->getSku();
        }
        return (string)$item->getProductId();
    }

}
