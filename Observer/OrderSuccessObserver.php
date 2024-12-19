<?php
/**
 * @copyright Copyright (c) 2024 Fusion Lab G.P
 * @package FusionLab_BestPrice
 */
namespace FusionLab\BestPrice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Google Analytics module observer
 *
 */
class OrderSuccessObserver implements ObserverInterface
{

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(
        LayoutInterface $layout,
    ) {
        $this->_layout = $layout;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
//        dump(1);
//        dd($orderIds);

        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $block = $this->_layout->getBlock('bestprice.tracking.success');

        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}
