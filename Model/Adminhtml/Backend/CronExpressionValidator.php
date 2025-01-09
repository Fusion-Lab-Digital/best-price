<?php

namespace FusionLab\BestPrice\Model\Adminhtml\Backend;

use Magento\Framework\Exception\LocalizedException;

class CronExpressionValidator extends \Magento\Framework\App\Config\Value
{

    /**
     * @return CronExpressionValidator
     * @throws LocalizedException
     */
    public function beforeSave(): CronExpressionValidator
    {
        if (!preg_match('/^(\*|[0-9,\-\/\*]+)\s+(\*|[0-9,\-\/\*]+)\s+(\*|[0-9,\-\/\*]+)\s+(\*|[0-9,\-\/\*]+)\s+(\*|[0-9,\-\/\*]+)$/', $this->getValue())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid cron expression. visit <a href="https://crontab.guru/" target="_blank">Crontab Guru</a> for valid expressions. ')
            );
        }
        return parent::beforeSave();
    }
}
