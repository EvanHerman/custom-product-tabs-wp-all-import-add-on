=== Custom Product Tabs for WooCommerce WP All Import Add-on ===
Contributors: yikesinc, liljimmi, yikesitskevin, soflyy, wpallimport
Donate link: http://yikesinc.com
Tags: woocommerce, product tabs, tabs, woo, wp all import, import, tab import, custom product tabs, yikes
Requires at least: 3.8
Tested up to: 5.0.0
Stable tag: 2.0.2
License: GPLv2 or later

This add-on extends Custom Product Tabs for WooCommerce to work with WP All Import. 

== Description ==

This plugin lets you import your [Custom Product Tabs for WooCommerce](https://wordpress.org/plugins/yikes-inc-easy-custom-woocommerce-product-tabs/) using [WP All Import](https://wordpress.org/plugins/wp-all-import/).

All of your saved custom product tabs will be loaded into the familiar WP All Import user interface. When defining your import, look for the Custom Product Tabs section. For each saved tab, you can choose to ignore it, apply it as is (as a saved tab), or apply it as a custom tab (and customize the tite/content).

Please be aware that importing custom product tabs will wipe out existing tabs for the product before applying the new ones.

<i>This plugin was developed by [YIKES, Inc.](https:/www.yikesinc.com) and the [WP All Import at Soflyy](http://soflyy.com/) team.</i>

> Note: This add-on plugin requires [Custom Product Tabs for WooCommerce](https://wordpress.org/plugins/yikes-inc-easy-custom-woocommerce-product-tabs/) and [WP All Import](https://wordpress.org/plugins/wp-all-import/) to work.

== Installation ==
1. Download the plugin .zip file and make note of where on your computer you downloaded it to.
2. In the WordPress admin (yourdomain.com/wp-admin) go to Plugins > Add New or click the "Add New" button on the main plugins screen.
3. On the following screen, click the "Upload Plugin" button.
4. Browse your computer to where you downloaded the plugin .zip file, select it and click the "Install Now" button.
5. After the plugin has successfully installed, click "Activate Plugin" and enjoy!

== Screenshots ==

1. The Custom Product Tabs for WooCommerce section of the WP All Import interface
2. An example of your options when applying saved tabs to your imported products - you can ignore, use the default, or customize a tab.
3. An example of customizing your tab

== Frequently Asked Questions ==

= What plugins do I need to use this plugin? =
This plugin relies on three other plugins: [Custom Product Tabs for WooCommerce](https://wordpress.org/plugins/yikes-inc-easy-custom-woocommerce-product-tabs/) to create your tabs, [WP All Import](https://wordpress.org/plugins/wp-all-import/) to import data, and [WP All Import's WooCommerce Extension](https://wordpress.org/plugins/woocommerce-xml-csv-product-import/) to import WooCommerce Products.

= Can I add tabs to existing products with this plugin? =
You can add tabs to existing products, but it will wipe out any tabs you currently have for that product.

= Can I add tabs to a subset of products in my import file? =
No. The options for applying/ignoring custom product tabs will be applied to all of the products in your import file.

= What is a saved tab vs. a custom tab? =
Saved and custom tabs come from the [Custom Product Tabs for WooCommerce](https://wordpress.org/plugins/yikes-inc-easy-custom-woocommerce-product-tabs/). A custom tab is defined for a single product, while a saved tab can be applied to as many products as you'd like.

== Changelog ==

= 2.0.2 - November 27th, 2018 =
* Updating the rapid-addon WP All Import API file.
* Minor updates related to WPCS.s

= 2.0.1 - November 1st, 2017 =
* Declaring compatibility with WooCommerce and WordPress

= 2.0.0 - October 13th, 2017 = 
* Compatibility with Custom Product Tabs 1.6.1+ - update to the way we do tab slugs

= 1.0.1 - June 26th, 2017 = 
* Fixed an issue where the ID of a saved tab was being saved incorrectly - saved tabs should now be properly recognized as saved tabs.

= 1.0.0 - April 24th, 2017 = 
* Hello World.