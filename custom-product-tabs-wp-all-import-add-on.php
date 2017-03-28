<?php
/**
 * Plugin Name: Custom Product Tabs WP All Import Add-on 
 * Plugin URI: http://www.yikesplugins.com
 * Description: Extend WP All Import's functionality to import your Custom Product Tabs for WooCommerce data
 * Author: YIKES, Inc., Kevin Utz
 * Author URI: http://www.yikesinc.com
 * Version: 1.0.0
 * Text Domain: custom-product-tabs-wp-all-import-add-on
 * Domain Path: languages/
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
	$ii = 1;
	foreach ( $saved_tabs as $saved_tab ) {

		$tab_title_field_name	= 'yikes_saved_tab_title_' . $ii;
		$tab_content_field_name = 'yikes_saved_tab_content_' . $ii;
		$tab_id_field_name		= 'yikes_saved_tab_id_' . $ii;
		$apply_tab_field_name	= 'yikes_apply_saved_tab_' . $ii;

		// We add the ID to the options array, not as a field (don't want it to be edited)
		$custom_product_tabs_for_woocommerce_addon->add_option( $tab_id_field_name, $saved_tab['tab_id'] );

		// Nest all of our fields within a nice accordion
		$custom_product_tabs_for_woocommerce_addon->add_options(
			null,
			'Saved Tab - ' . $saved_tab['tab_id'] . ': ' . $saved_tab['tab_title'],
			array(

				// Add a saved tab's title as a text field, pre-populate text field with tab title content
				// $custom_product_tabs_for_woocommerce_addon->add_field( $tab_title_field_name, 'Tab Title:', 'text', null, null, false, $saved_tab['tab_title'] ),

				// Add a saved tab's content as a textarea field, pre-populate textarea with tab content
				// $custom_product_tabs_for_woocommerce_addon->add_field( $tab_content_field_name, 'Tab Content:', 'wp_editor', null, null, false, stripslashes( $saved_tab['tab_content'] ) ),

				// Radio buttons - apply tab vs. ignore tab
				$custom_product_tabs_for_woocommerce_addon->add_field( $apply_tab_field_name, 'Apply this tab to imported products?', 'radio', 
					array( 
						'ignore' => array( 'No - Ignore Tab' ),
						'apply_saved' => array( 'Yes - Used Saved Tab Title & Content' ),
						'apply_custom' => 
							array( 
								'Yes - Edit Tab Title & Content',
								$custom_product_tabs_for_woocommerce_addon->add_field( $tab_title_field_name, 'Tab Title:', 'text', null, null, false, $saved_tab['tab_title'] ),
								$custom_product_tabs_for_woocommerce_addon->add_field( $tab_content_field_name, 'Tab Content:', 'wp_editor', null, null, false, stripslashes( $saved_tab['tab_content'] ) ),
							),
					),
					'If you choose no, this tab will not be added to the product. <br> If you choose "Apply as Saved Tab," this tab will be added as a saved tab. Any text you add will be overwritten with the saved tab\'s value. If you choose "Apply as Custom Tab" the tab will be added, not as a saved tab, and you can customize the content.' )
			)
		);

		$custom_product_tabs_for_woocommerce_addon->add_text( '<hr>' );
		
		$ii++;
	}
} else {

	// If we don't have saved tabs, just let the user know
	$custom_product_tabs_for_woocommerce_addon->add_text( '<p>You need to set up saved tabs in order to apply them to your products.</p>' );
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
	$update_post_meta  = false;
	$update_tab_option = false;

	// Grab our saved tab data
	$saved_tabs			= get_option( 'yikes_woo_reusable_products_tabs' );
	$saved_tabs_applied = get_option( 'yikes_woo_reusable_products_tabs_applied', array() );

	// Calculate the count here so we do it only once
	$saved_tabs_count = count( $saved_tabs );

	// Fetch the current tabs - we'll append to them if we need to
	$current_tabs = cpt4woo_fetch_product_tabs_for_post( $post_id );

	for ( $i = 1; $i <= $saved_tabs_count; $i++ ) {

		// Verify that we have all four pieces of info for this tab
		if (  ! isset( $data['yikes_saved_tab_title_' . $i ] ) || ! isset( $data['yikes_saved_tab_content_' . $i ] ) || ! isset( $import_options['options']['yikes_saved_tab_id_' . $i ] ) || ! isset( $data['yikes_apply_saved_tab_' . $i ] ) ) {
			continue;
		}

		// 0 = do not apply saved tab
		// 1 = apply as saved tab
		// 2 = apply as custom tab
		$action = $data['yikes_apply_saved_tab_' . $i];

		// We don't process these
		if ( $action !== '1' && $action !== '2' ) {
			continue;
		}

		// If $action is 1 or 2, we're going to add the tab to the meta field
		$update_meta  = true;

		// Set up our tab data
		$tab_title	  = $data['yikes_saved_tab_title_' . $i ];
		$tab_content  = $data['yikes_saved_tab_content_' . $i ];
		$tab_id		  = $import_options['options']['yikes_saved_tab_id_' . $i ];
		$saved_tab_id = cpt4woo_create_tab_id_string( $tab_title );

		// The array of arrays that we will add as our 'yikes_woo_products_tabs' post meta
		$current_tabs[] = array(
			'title'		=> $tab_title,
			'content'	=> $tab_content,
			'id'		=> $saved_tab_id
		);

		// If action is 1, we also add the tab as a saved tab
		if ( $action === '1' ) {
			$update_tab_option = true;

		 	// The array that we will update our 'yikes_woo_reusable_products_tabs_applied' with
			$saved_tabs_applied[$post_id][$tab_id] = array(
				'post_id' 			=> $post_id,
				'reusable_tab_id'	=> $tab_id,
				'tab_id'			=> $saved_tab_id
			);
		}
	}

	// Add the tab to the product's tabs
	if ( $update_meta === true ) {
		update_post_meta( $post_id, 'yikes_woo_products_tabs', $current_tabs );
	}

	// Add the tab to our array of applied saved tabs
	if ( $update_tab_option === true ) {
		update_option( 'yikes_woo_reusable_products_tabs_applied', $saved_tabs_applied );
	}

}

function cpt4woo_create_tab_id_string( $tab_title ) {

	// Convert to lowercase
	$saved_tab_id = strtolower( $tab_title );

	// Remove: non-alphas, numbers, underscores, whitespace
	$saved_tab_id = preg_replace( "/[^\w\s]/", '', $saved_tab_id );

	// Replace: underscores with dashes
	$saved_tab_id = preg_replace( "/_+/", ' ', $saved_tab_id );

	// Replace: all multiple spaces with single dashes
	$saved_tab_id = preg_replace( "/\s+/", '-', $saved_tab_id );

	return $saved_tab_id;
}

function cpt4woo_fetch_product_tabs_for_post( $post_id ) {
	$current_products_tabs = get_post_meta( $post_id, 'yikes_woo_products_tabs', true );
	$current_products_tabs = empty( $current_products_tabs ) ? array() : $current_products_tabs;
	return $current_products_tabs;
}