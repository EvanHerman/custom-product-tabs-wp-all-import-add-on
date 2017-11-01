<?php
/**
 * Plugin Name: Custom Product Tabs WP All Import Add-on 
 * Plugin URI: http://www.yikesplugins.com
 * Description: Extend WP All Import's functionality to import your Custom Product Tabs for WooCommerce data
 * Author: YIKES, Inc.
 * Author URI: http://www.yikesinc.com
 * Version: 2.0.1
 * Text Domain: custom-product-tabs-wp-all-import-add-on
 * Domain Path: languages/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.2
 *
 * Copyright: (c) 2017 YIKES Inc.
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

require_once( 'rapid-addon.php' );

// Initialize our add on
$custom_product_tabs_for_woocommerce_addon = new RapidAddon( 'Custom Product Tabs', 'custom_product_tabs_for_woocommerce_addon' );

// Verify the WP All Import plugin is active
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
if ( ! is_plugin_active( 'wp-all-import/wp-all-import-php' ) ) {
	$custom_product_tabs_for_woocommerce_addon->admin_notice( __( "Custom Product Tabs WP All Import Add-on requires <a href='http://wordpress.org/plugins/wp-all-import'>WP All Import</a> to be installed and active.", 'custom-product-tabs-wp-all-import-add-on' ) );
}

// Get our saved tabs
$saved_tabs = get_option( 'yikes_woo_reusable_products_tabs' );

// If we have saved tabs...
if ( ! empty( $saved_tabs ) ) {

	// Loop through the saved tabs and add a text field for each tab title and tab content
	foreach ( $saved_tabs as $saved_tab ) {

		$tab_title_field_name	= 'yikes_saved_tab_title_' . $saved_tab['tab_id'];
		$tab_content_field_name = 'yikes_saved_tab_content_' . $saved_tab['tab_id'];
		$apply_tab_field_name	= 'yikes_apply_saved_tab_' . $saved_tab['tab_id'];

		// Nest all of our fields within a nice accordion
		$custom_product_tabs_for_woocommerce_addon->add_options(
			null,
			$saved_tab['tab_title'],
			array(

				// Radio buttons - ignore tab, apply saved, apply custom (show title/content fields if apply custom is chosen)
				$custom_product_tabs_for_woocommerce_addon->add_field( $apply_tab_field_name, __( 'Apply this tab to imported products?', 'custom-product-tabs-wp-all-import-add-on' ), 'radio', 
					array( 
						'ignore' => array( __( 'No - Ignore Tab', 'custom-product-tabs-wp-all-import-add-on' ) ),
						'apply_saved' => array( __( 'Yes - Use Saved Tab Title & Content', 'custom-product-tabs-wp-all-import-add-on' ) ),
						'apply_custom' => 
							array( 
								__( 'Yes - Edit Tab Title & Content', 'custom-product-tabs-wp-all-import-add-on' ),
								$custom_product_tabs_for_woocommerce_addon->add_field( $tab_title_field_name, 'Tab Title:', 'text', null, null, false, $saved_tab['tab_title'] ),
								$custom_product_tabs_for_woocommerce_addon->add_field( $tab_content_field_name, 'Tab Content:', 'wp_editor', null, null, false, stripslashes( $saved_tab['tab_content'] ) ),
							),
					),
					sprintf( __( '"No - Ignore Tab" - This tab will not be added to your products. %1$s "Use Saved Tab Title & Content" - This tab will be added as a saved tab. %1$s "Yes - Edit Tab Title & Content" -  This tab will be added as a custom tab. You can customize the content.', 'custom-product-tabs-wp-all-import-add-on' ), '<br>' ) )
			)
		);

		$custom_product_tabs_for_woocommerce_addon->add_text( '<hr>' );

	}

} else {

	// If we don't have saved tabs, just let the user know
	$custom_product_tabs_for_woocommerce_addon->add_text( '<p>' . __( 'You need to set up saved tabs in order to apply them to your products.', 'custom-product-tabs-wp-all-import-add-on' ) . '</p>' );
}

// Define the import function
$custom_product_tabs_for_woocommerce_addon->set_import_function( 'cpt4woo_addon_import' );

// Set our add-on to run only for WooCommerce products
$custom_product_tabs_for_woocommerce_addon->run(
	array(
		'post_types' => array( 'product' ),
	)
);


function cpt4woo_addon_import( $post_id, $data, $import_options ) {

	global $custom_product_tabs_for_woocommerce_addon;

	// Simply return if we can't/shouldn't update this post meta field
	if ( ! $custom_product_tabs_for_woocommerce_addon->can_update_meta( 'yikes_woo_products_tabs', $import_options ) ) {
		return;
	}

	// Set up our defaults
	$update_tab_option = false;

	// Grab our saved tab data
	$saved_tabs			= get_option( 'yikes_woo_reusable_products_tabs' );
	$saved_tabs_applied = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );

    // Fetch the current tabs - we'll append to them if we need to
    $current_tabs = array();

    if ( ! empty( $saved_tabs ) ) {

        foreach ( $saved_tabs as $tab ) {

            // Verify that we have all four pieces of info for this tab
            if (  ! isset( $data['yikes_saved_tab_title_' . $tab['tab_id'] ] ) || ! isset( $data['yikes_saved_tab_content_' . $tab['tab_id'] ] ) || ! isset( $data['yikes_apply_saved_tab_' . $tab['tab_id'] ] ) ) {
                continue;
            }

            // 'ignore' = do not apply saved tab
            // 'apply_saved' = apply as saved tab
            // 'apply_custom' = apply as custom tab
            $action = $data['yikes_apply_saved_tab_' . $tab['tab_id']];

            // We don't process these
            if ( $action === 'ignore' ) {
                continue;
            }

            // Set up our tab data
            $tab_title	  = $data['yikes_saved_tab_title_' . $tab['tab_id'] ];
            $tab_content  = $data['yikes_saved_tab_content_' . $tab['tab_id'] ];
            $saved_tab_id = urldecode( sanitize_title( $tab_title ) );

            if ( $action == 'apply_custom' ) {
                // The array of arrays that we will add as our 'yikes_woo_products_tabs' post meta
                $current_tabs[] = array(
                    'title'		=> $tab_title,
                    'content'	=> $tab_content,
                    'id'		=> $saved_tab_id
                );
            }

            // If action is apply_saved, we also add the tab as a saved tab
            if ( $action === 'apply_saved' ) {

                $current_tabs[] = array(
                    'title'		=> $tab['tab_title'],
                    'content'	=> $tab['tab_content'],
                    'id'		=> $saved_tab_id
                );

                $update_tab_option = true;
                // The array that we will update our 'yikes_woo_reusable_products_tabs_applied' with
                $saved_tabs_applied[$post_id][$tab['tab_id']] = array(
                    'post_id' 			=> $post_id,
                    'reusable_tab_id'	=> $tab['tab_id'],
                    'tab_id'			=> $saved_tab_id
                );
            }
        }
    }

	// Add the tab to the product's tabs
    update_post_meta( $post_id, 'yikes_woo_products_tabs', $current_tabs );

	// Add the tab to our array of applied saved tabs
	if ( $update_tab_option === true ) {
		update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
	}

}

/**
* Fetch the post meta field `yikes_woo_products_tabs` for this product
*
* @param  int   | $post_id
*
* @return The array of custom product tabs || empty array
*/
function cpt4woo_fetch_product_tabs_for_post( $post_id ) {
	$current_products_tabs = get_post_meta( $post_id, 'yikes_woo_products_tabs', true );
	$current_products_tabs = empty( $current_products_tabs ) ? array() : $current_products_tabs;
	return $current_products_tabs;
}

/**
* i18n
*/
function cpt4woo_load_text_domain() {
	load_plugin_textdomain(
		'custom-product-tabs-wp-all-import-add-on',
		false,
		dirname( __FILE__  ) . '/languages/'
	);
}

add_action( 'plugins_loaded', 'cpt4woo_load_text_domain' );