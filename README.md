<a href="https://fusionlab.gr?utm_source=github&utm_medium=bestprice&utm_campaign=module" target="_blank">
<img align="center" width="250" height="100" src="https://fusionlab.gr/fusion-lab-logo-neg-cropped.svg"/>
</a>

# Fusion Lab - Best Price Magento 2 Extension

![Magento 2](https://img.shields.io/badge/Magento-2.4.x-orange.svg) ![License](https://img.shields.io/badge/license-Apache2.0-blue.svg)

## 📌 Overview

Enable <b>[BestPrice](https://bestprice.gr) 360 Analytics</b> and Integrate your Magento 2 Store with BestPrice with the built-in <b>XML Product Export</b>.

The XML Product Export supports multi-store view environments and is fully compatible with Magento 2's MSI (Multi-Source Inventory) system. It is also optimized to efficiently handle large volumes of data (tested with ~250k products).


## ⚡ Features

- BestPrice 360 Analytics
- - With Multishipping Checkout Support
- - Configurable Product Identifier (sku or product id)
- Product XML Export
- - Optimized Export
- - Multi-Website Support
- - Admin Configuration

## 🛠️ Installation

### Install via Composer 2.x
We recommend to install this module via a compatible version of [Composer 2.x](https://getcomposer.org/download/) for your Magento 2 Installation.

See your [Magento 2 Requirements here](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/system-requirements). 
```bash
composer require fusionlab-digital/best-price
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

![image](https://github.com/user-attachments/assets/507d5c91-0f42-4116-965f-f7e7fa90cf52)

Save. Done!

## 🗂️ XML Product Export Setup

Open the Admin and navigate to <b>Menu -> FusionLab -> BestPrice -> XML Export Settings.</b>


![image](https://github.com/user-attachments/assets/3ab3ce25-50cb-4158-8df7-028f03b16524)


1. Enable the module and choose for which stores you want to export.
2. Expand Attributes Mapping and apply your configuration options for the attributes
3. Expand Filters to apply any logic like Exclude Categories etc.

Exports can be found in ```pub/fusionlab_feeds``` folder of your installation.
And also they can be viewed in the admin

### Configuration Documentation

| Field                                   | Area                                      | Documentation |
|-----------------------------------------|-------------------------------------------|-|
| Enable Module?                          | General Settings                          | Enables the 360 Analytics Integraton for the current website scope
| 360 Analytics Id                        | General Settings                          | Provided by BestPrice. Your unique identifier for BestPrice 360 Analytics
| Product Identifier                      | General Settings                          | Selects where to send Product Id or Product Sku in the BestPrice events and also is being used for ID in the Product Export
| XML Files                               | XML Export Settings                       | View For XML's with metadata from the filesystem.
| Enable Export?                          | XML Export Settings                       | Enables or Disables Export for the current Store View Scope.
| Collection Batch Size                   | XML Export Settings                       | The batch size of the products that will be requested in each itteration to be computed. Increase this value according to your Server's capabilities.
| Store View                              | XML Export Settings                       | Multi Select of the Store Views to Export Product XML.
| Cron Expression                         | XML Export Settings                       | Configurable Cron Expression. Default value is "every 2 hours"
| Get Availability via Product Attribute? | XML Export Settings -> Attributes Mapping | Configures if the Product Availability will be fetched from the Product or not.
| Availability Attribute?                 | XML Export Settings -> Attributes Mapping | Product Attribute that will be used for the availability.
| Product Availability Text               | XML Export Settings -> Attributes Mapping | Flat value for all products that will be used as availability.
| Get Brand via Product Attribute?	       | XML Export Settings -> Attributes Mapping | Configures if the Product Availability will be fetched from the Product or not.
| Brand Attribute?	                       | XML Export Settings -> Attributes Mapping | Product Attribute that will be used for the Brand.
| Product Brand Text                      | XML Export Settings -> Attributes Mapping | Flat value for all products that will be used as Brand.
| MPN Attribute	                          | XML Export Settings -> Attributes Mapping | Attribute that will be resolved as the MPN from the Product.
| Size Attribute	                         | XML Export Settings -> Attributes Mapping | Attribute that will be resolved as the Size from the Product.
| Color Attribute	                        | XML Export Settings -> Attributes Mapping | Attribute that will be resolved as the Color from the Product.
| Categories to Exclude	                  | XML Export Settings -> Filters            | Products that are included from the selected categories will be excluded from the XML. <b>If Parent level Category is excluded, all products from this category and all categories below will also be excluded.</b>
| Includes Out of Stock?	                 | XML Export Settings -> Filters            | Whether to Include Out of Stock Products or not.
| Include with Flag?	                     | XML Export Settings -> Filters            | To include only products that have a certain attribute set to true.
| Flag Attribute	                         | XML Export Settings -> Filters            | The boolean attribute. Set this attribute to yes to all the products you want to include to the XML. 




## 📄 License

This module is licensed under the Apache 2.0 License. See [LICENSE](LICENSE) for details.


## 📩 Support

For any issues, feature requests, or inquiries, please open an issue on [GitHub Issues](https://github.com/Fusion-Lab-Digital/m2.core/issues), contact us at info@fusionlab.gr, or visit our website at [fusionlab.gr](<a href="https://fusionlab.gr?utm_source=github&utm_medium=bestprice&utm_campaign=module" target="_blank">
<img align="center" width="250" height="100" src="https://fusionlab.gr/fusion-lab-logo-neg-cropped.svg"/>
</a>) for more information.

