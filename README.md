<img align="center" width="250" height="100" src="https://fusionlab.gr/fusion-lab-logo-neg-cropped.svg"/>


# Fusion Lab - Best Price Magento 2 Extension

![Magento 2](https://img.shields.io/badge/Magento-2.4.x-orange.svg) ![License](https://img.shields.io/badge/license-Apache2.0-blue.svg)

## 📌 Overview

Enable <b>[BestPrice](https://bestprice.gr) 360 Analytics</b> and Integrate your Magento 2 Store with BestPrice with the built-in <b>XML Product Export</b>.

## ⚡ Features

- BestPrice 360 Analytics
- - With Multishipping Checkout Support
- - Configurable Product Identifier (sku or product id)
- Product XML Export
- - Optimized Export (250k products ~ 15 minutes export time)
- - Multi-Website Support
- - Admin Configuration

## 🛠️ Installation

### Install via Composer 2.x
We recommend to install this module via a compatible version of [Composer 2.x](https://getcomposer.org/download/) for your Magento 2 Installtion.

See your [Magento 2 Requirements here](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/system-requirements). 
```bash
composer require fusionlab/best-price
php bin/magento module:enable FusionLab_BestPrice FusionLab_Core
php bin/magento setup:upgrade
php bin/magento s:d:c
php bin/magento s:s:d {Your Themes}
php bin/magento cache:flush
```

### Manual Installation (not recommended)
1. This module has a dependency to [FusionLab_Core](https://github.com/Fusion-Lab-Digital/m2.core) which you must first install. See the github page for installation instructions. 
2. Download the module and extract it into `app/code/FusionLab/BestPrice`
2. Run the following Magento CLI commands:
```bash
php bin/magento module:enable FusionLab_BestPrice FusionLab_Core
php bin/magento setup:upgrade
php bin/magento s:d:c
php bin/magento s:s:d {Your Themes}
php bin/magento cache:flush
```

## 🚀 360 Analytics Setup

Open the Admin and navigate to <b>Menu -> FusionLab -> BestPrice -> General Settings.</b> 

Enable the Module and Provide your Best Price 360 Analytics Id

Save. Done!

## 🗂️ XML Product Export Setup

Open the Admin and navigate to <b>Menu -> FusionLab -> BestPrice -> XML Export Settings.</b>

1. Enable the module and choose for which stores you want to export.
2. Expand Attributes Mapping and apply your configuration options for the attributes
3. Expand Filters to apply any logic like Exclude Categories etc.

Exports can be found in ```pub/fusionlab_feeds``` folder of your installation.
And also they can be viewed in the admin


## 📄 License

This module is licensed under the Apache 2.0 License. See [LICENSE](LICENSE) for details.

## 📩 Support

For any issues or feature requests, please open an issue on [GitHub Issues](https://github.com/Fusion-Lab-Digital/best-price/issues) or contact info@fusionlab.gr.
