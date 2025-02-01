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

    const XML_PATH_BESTPRICE_XML_ENABLED = 'fusionlab_bestprice/xml/enable';

    const XML_PATH_BESTPRICE_XML_BATCH_SIZE = 'fusionlab_bestprice/xml/batch_size';

    const XML_PATH_BESTPRICE_XML_STORE_VIEW = 'fusionlab_bestprice/xml/store_view';

    const XML_PATH_BESTPRICE_XML_MAPPING_AVAIILABILITY_FROM_PRODUCT = 'fusionlab_bestprice/xml/mapping/availability_from_product';

    const XML_PATH_BESTPRICE_XML_MAPPING_AVAIILABILITY_PRODUCT_ATTRIBUTE = 'fusionlab_bestprice/xml/mapping/availability_product_attribute';

    const XML_PATH_BESTPRICE_XML_MAPPING_FIXED_AVAILABILITY = 'fusionlab_bestprice/xml/mapping/fixed_availability';

    const XML_PATH_BESTPRICE_XML_MAPPING_BRAND_FROM_PRODUCT = 'fusionlab_bestprice/xml/mapping/brand_from_product';

    const XML_PATH_BESTPRICE_XML_MAPPING_BRAND_PRODUCT_ATTRIBUTE = 'fusionlab_bestprice/xml/mapping/brand_product_attribute';

    const XML_PATH_BESTPRICE_XML_MAPPING_FIXED_BRAND = 'fusionlab_bestprice/xml/mapping/fixed_brand';

    const XML_PATH_BESTPRICE_XML_MAPPING_MPN_PRODUCT_ATTRIBUTE = 'fusionlab_bestprice/xml/mapping/mpn_product_attribute';

    const XML_PATH_BESTPRICE_XML_MAPPING_SIZE_PRODUCT_ATTRIBUTE = 'fusionlab_bestprice/xml/mapping/size_product_attribute';

    const XML_PATH_BESTPRICE_XML_MAPPING_COLOR_PRODUCT_ATTRIBUTE = 'fusionlab_bestprice/xml/mapping/color_product_attribute';

    const XML_PATH_BESTPRICE_XML_FILTERS_EXCLUDE_CATEGORIES = 'fusionlab_bestprice/xml/filters/exclude_categories';
    const XML_PATH_BESTPRICE_XML_FILTERS_INCLUDE_OUT_OF_STOCK = 'fusionlab_bestprice/xml/filters/include_out_of_stock';
    const XML_PATH_BESTPRICE_XML_FILTERS_INCLUDE_WITH_ATTRIBUTE = 'fusionlab_bestprice/xml/filters/include_with_attribute';
    const XML_PATH_BESTPRICE_XML_FILTERS_FLAG_ATTRIBUTE = 'fusionlab_bestprice/xml/filters/flag_attribute';

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
     * @return string|null
     */
    public function getProductIdentifier(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_PRODUCT_IDENTIFIER, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function getIsXMLExportEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getXMLBatchSize(): int
    {
        $batchSize = (int) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_BATCH_SIZE);
        if ($batchSize < 500) {
            $batchSize = 500;
        }
        return $batchSize;
    }

    /**
     * @return array
     */
    public function getStoreViewsToExport(): array
    {
        $result = [];
        $storeIds = $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_STORE_VIEW);
        if ($storeIds) {
            $result = explode(",", $storeIds);
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function getIsAvailabilityFromProductAttribute(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_AVAIILABILITY_FROM_PRODUCT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getProductAvailabilityAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_AVAIILABILITY_PRODUCT_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getFixedAvailabilityValue(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_FIXED_AVAILABILITY, ScopeInterface::SCOPE_STORE) ?? "Σε απόθεμα";
    }

    public function getIsBrandFromProductAttribute(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_BRAND_FROM_PRODUCT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getProductBrandAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_BRAND_PRODUCT_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getFixedBrandValue(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_FIXED_BRAND, ScopeInterface::SCOPE_STORE) ?? 'OEM';
    }

    /**
     * @return string
     */
    public function getMpnProductAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_MPN_PRODUCT_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getSizeProductAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_SIZE_PRODUCT_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getColorProductAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_MAPPING_COLOR_PRODUCT_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getExcludedCategoryIds(): array
    {
        $result = [];
        $categories = $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_FILTERS_EXCLUDE_CATEGORIES);
        if ($categories) {
            $result = explode(",", $categories);
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function shouldIncludeOutOfStockProducts(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_FILTERS_INCLUDE_OUT_OF_STOCK, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function shouldIncludeWithAttributes(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_FILTERS_INCLUDE_WITH_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getProductFlagAttribute(): ?string
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_BESTPRICE_XML_FILTERS_FLAG_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }
}
