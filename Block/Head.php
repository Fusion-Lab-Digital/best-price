<?php
/**
 * @author Vasilis Neris
 * @copyright Copyright (c) 2024 Fusion Lab G.P
 * @package FusionLab_BestPrice
 */
namespace FusionLab\BestPrice\Block;

use FusionLab\BestPrice\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Head extends Template
{

    protected $_template = 'FusionLab_BestPrice::head.phtml';

    private ConfigProvider $configProvider;

    /**
     * @param ConfigProvider $configProvider
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ConfigProvider   $configProvider,
        Template\Context $context,
        array            $data = []
    )
    {
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
