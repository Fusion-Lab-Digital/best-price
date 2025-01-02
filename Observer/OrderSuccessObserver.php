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
namespace FusionLab\BestPrice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

class OrderSuccessObserver implements ObserverInterface
{

    protected LayoutInterface $_layout;

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
    public function execute(Observer $observer):void
    {
        $orderIds = $observer->getEvent()->getOrderIds();

        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $block = $this->_layout->getBlock('bestprice.tracking.success');

        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}
