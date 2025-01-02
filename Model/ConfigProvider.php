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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{

    const XML_PATH_BESTPRICE_ENABLE = 'fusionlab_bestprice/general/enable';

    const XML_PATH_BESTPRICE_ANALYTICS_ID = 'fusionlab_bestprice/general/analytics_id';

    const XML_PATH_BESTPRICE_PRODUCT_IDENTIFIER = 'fusionlab_bestprice/general/product_identifier';

    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getAnalyticsId(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_ANALYTICS_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getProductIdentifier(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_PRODUCT_IDENTIFIER, ScopeInterface::SCOPE_STORE);
    }
}
