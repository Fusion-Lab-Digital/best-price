<?php
/**
 * @copyright Copyright (c) 2024 Fusion Lab G.P
 * @package FusionLab_BestPrice
 */
namespace FusionLab\BestPrice\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;

class ProductIdentifier implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray():array
    {
        return [
            ['value' => 'id', 'label' => __('ID')],
            ['value' => 'sku', 'label' => __('SKU')],
        ];
    }

}
