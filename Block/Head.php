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

class Head extends Template
{

    protected $_template = 'FusionLab_BestPrice::head.phtml';

    private ConfigProvider $configProvider;

    /**
     * @param ConfigProvider $configProvider
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        ConfigProvider   $configProvider,
        Template\Context $context,
        array            $data = []
    ) {
        $this->configProvider = $configProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getAnalyticsId(): string
    {
        return $this->configProvider->getAnalyticsId();
    }
}
