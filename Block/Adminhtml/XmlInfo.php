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

namespace FusionLab\BestPrice\Block\Adminhtml;

use FusionLab\BestPrice\Model\ConfigProvider;
use FusionLab\BestPrice\Model\XmlGenerator;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\StoreManagerInterface;

class XmlInfo extends Field
{

    protected $_template = 'FusionLab_BestPrice::xml-info.phtml';

    private ConfigProvider $configProvider;

    private Filesystem $filesystem;

    private StoreManagerInterface $storeManager;

    /**
     * @param Context $context
     * @param ConfigProvider $configProvider
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context             $context,
        ConfigProvider      $configProvider,
        Filesystem          $filesystem,
        StoreManagerInterface $storeManager,
        array               $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->configProvider = $configProvider;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * @return XmlInfo
     */
    protected function _prepareLayout()
    {
        if (!$this->configProvider->getIsXMLExportEnabled()) {
            $this->setTemplate(null);
        }
        return parent::_prepareLayout();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getXmls(): array
    {
        $xmls = [];
        $pubFolder = $this->filesystem->getDirectoryRead(DirectoryList::PUB);
        if (!is_dir($pubFolder->getAbsolutePath(XmlGenerator::XML_WRITE_DIR))) {
            return $xmls;
        }

        $xmlFolder = $pubFolder->getAbsolutePath(XmlGenerator::XML_WRITE_DIR);

        $files = scandir($xmlFolder);

        foreach ($files as $file) {
            if (str_contains($file, '.xml')) {
                $xmls[] = [
                    'link' => $this->storeManager->getStore()->getBaseUrl() . XmlGenerator::XML_WRITE_DIR . DIRECTORY_SEPARATOR . $file,
                    'modded' => date('Y-m-d H:i:s', filectime($xmlFolder . DIRECTORY_SEPARATOR . $file)),
                    'name' => $file,
                    'size' => number_format(filesize($xmlFolder . DIRECTORY_SEPARATOR . $file) / (1024 * 1024), 2) . ' MB',

                ];
            }
        }

        return $xmls;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->toHtml();
    }
}
