=== Simple Menu Order Column ===
Contributors: Chillcode
Tags: menu order, pages, media, posts, products
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Expose menu order column on your dashboard listings.

== Description ==

Every WP_Post (page, attachment, post, woo product) has a menu_order column and this plugin allows you to modify it directly on your dashboard listings.

= Features =

* Change menu order on WP_Post types like pages, attachments, posts & woo products.

== Prerequisites ==

* [**WordPress**](https://wordpress.org)

= Pricing =

Free

== Installation ==

= Automatic =

1. **Access WordPress Admin**: Log in to your **WordPress** admin dashboard.
2. **Navigate to Plugins**: Once logged in, go to the "Plugins" section on the left-hand menu of the **WordPress** admin dashboard.
3. **Click on "Add New"**: Within the Plugins section, click on the "Add New" button. This will take you to the "Add Plugins" page.
4. **Search for the Plugin**: In the search bar on the top right, type in **Simple Menu Order Column**. **WordPress** will automatically search for plugins matching your search query.
5. **Find the Plugin**: Once you've found **Simple Menu Order Column**, click on the "Install Now" button below the plugin's name and description.
6. **Activate Plugin**: After the installation is complete, you'll see an "Activate" button. Click on it to activate the plugin on your **WordPress** site.

That's it! You've successfully installed and activated **Simple Menu Order Column** plugin automatically from within the **WordPress** admin dashboard.

= Manual =

1. **Download the Plugin**: Begin by downloading the plugin from **WordPress**. This is typically a zip file containing all the necessary files for the plugin.
2. **Access WordPress Admin**: Log in to your **WordPress** admin dashboard. This is usually accessed by adding "/wp-admin" to the end of your website's URL and entering your credentials.
3. **Navigate to Plugins**: Once logged in, go to the "Plugins" section on the left-hand menu of the **WordPress** admin dashboard.
4. **Click on "Add New"**: Within the Plugins section, click on the "Add New" button. This will take you to the "Add Plugins" page.
5. **Upload Plugin**: On the "Add Plugins" page, click on the "Upload Plugin" button at the top of the page.
6. **Choose File**: Click on the "Choose File" button and select the plugin zip file you downloaded in step 1 from your computer.
7. **Install Now**: After selecting the plugin file, click on the "Install Now" button. **WordPress** will now upload and install the plugin from the zip file.
8. **Activate Plugin**: Once the plugin is successfully installed, you will see a success message. Now, click on the "Activate Plugin" link to activate the plugin on your **WordPress** site.

That's it! You've successfully installed and activated **Simple Menu Order Column** plugin manually from within the **WordPress** admin dashboard.

== Configuration ==

Once installed you will see an input box on every listing item.

== Usage ==

1. **Access WordPress Admin**: Log in to your **WordPress** admin dashboard. This is usually accessed by adding "/wp-admin" to the end of your website's URL and entering your credentials.
2. **Navigate to Posts, Media, Pages or Products**: After logging in, navigate to any listing page based on WP_Post, such as Posts, Media, Pages, or Products (if you've installed WooCommerce). You can find these sections in the left-hand menu of the **WordPress** admin dashboard.
3. **Reordering items**: Each list item will have a new input box with a menu_order value *. Simply change the value and press Enter to reorder the items. Negative values are also acceptable. For example, -1 will be sorted before 1 or 0.

* If Order column is not present [Manage screen options](https://wordpress.org/documentation/article/administration-screens/#screen-options/) and enable Order Column.

== Changelog ==

= 1.0.1 2024-07-19 =

**Simple Menu Order Column**

* Add - Added license.txt and LICENSE.md.
* Fix - Remove WC referenes from tags. [#1](https://github.com/ChillCode/simple-menu-order-column/issues/1)
* Update - Added changelog.txt.

== Upgrade Notice ==

= 1.0.1 =
Fix false positive with Woo HPOS.

== Screenshots ==

1. Order Posts.
2. Order Media.
3. Order Products.

== Frequently Asked Questions ==

= Does Simple Menu Order Column work with any WP_Post type? = 

Can work with any WP_Post type but may require some additional coding.

= How to show/hide the column on some listigs?

[Manage screen options](https://wordpress.org/documentation/article/administration-screens/#screen-options/)

= How to delete all plugin data?

1. Uninstall **Simple Menu Order Column** to erase all data. WP_Post menu_order column will not be modified.

[Manage plugins](https://wordpress.org/documentation/article/manage-plugins/)

