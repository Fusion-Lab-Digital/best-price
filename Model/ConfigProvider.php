<?php
/**
 * @copyright Copyright (c) 2024 Fusion Lab G.P
 * @package FusionLab_BestPrice
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
