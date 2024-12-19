<?php
/**
 * @copyright Copyright (c) 2024 Fusion Lab G.P
 * @package FusionLab_BestPrice
 */
namespace FusionLab\BestPrice\Model\Adminhtml\Backend;


use Magento\Framework\Exception\LocalizedException;

class AnalyticsIdValidator extends \Magento\Framework\App\Config\Value
{

    /**
     * @return AnalyticsIdValidator
     * @throws LocalizedException
     */
    public function beforeSave(): AnalyticsIdValidator
    {

        if (!preg_match('/^BP-[A-Za-z0-9-]+$/', $this->getValue())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid Best Price 360 Analytics Id.')
            );
        }

        return parent::beforeSave();
    }


}
