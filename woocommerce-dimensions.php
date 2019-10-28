<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Dimensions Tab
 * Description:       Display dimensions of variations below description tab.
 * Version:           1.0
 * Author:            Micah Robinson
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-dimensions
 WC tested up to: 5.1.1
 */

/** Die if accessed directly
*/

defined( 'ABSPATH' ) || exit;

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	// ONLY RUN IF WOOCOMMERCE IS ACTIVE...

	// Hides the product's weight and dimension in single product page
	add_filter( 'wc_product_enable_dimensions_display', '__return_false' );

	// Callback Function
	function woo_dimensions_tab_content( $content ) {
		$returnContent='';
		// Check if we're inside the main loop in a single product page.
		if ( is_product() ) {
			global $product;
			$dimensionsContent= '';
			if ($product->get_type() == 'simple') {
				$dimensionsContent.= '<table class="woocommerce-product-attributes shop_attributes">';
				if ( $product->has_weight() ) {
					$dimensionsContent.= '<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--weight">';
					$dimensionsContent.= '<th class="woocommerce-product-attributes-item__label">Weight</th>';
					$dimensionsContent.= '<td class="woocommerce-product-attributes-item__value">'. wp_kses_post( wc_format_weight( $product->get_weight() ) ) .'</td>';
					$dimensionsContent.= '</tr>';
				}
				if ( $product->has_dimensions() ) {
					$dimensionsContent.= '<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--dimensions">';
					$dimensionsContent.= '<th class="woocommerce-product-attributes-item__label">Dimensions</th>';
					$dimensionsContent.= '<td class="woocommerce-product-attributes-item__value">'. wp_kses_post( wc_format_dimensions( $product->get_dimensions( false ) ) ) .'</td>';
					$dimensionsContent.= '</tr>';
				}
				$dimensionsContent.= '</table>';
			} else if ($product->get_type() == 'variable') {
				$variations = $product->get_available_variations();
				$foundSizes= array();
				$dimensionsContent.= '<table class="shop_dimensions">';
				foreach ( $variations as $key => $value ) {
					if ( ($value['dimensions_html'] !== 'N/A' || $value['weight_html'] !== 'N/A') && (isset($value['attributes']['attribute_pa_size']) ) ) {
						if (!in_array($value['attributes']['attribute_pa_size'], $foundSizes)) {
							$foundSizes[]= $value['attributes']['attribute_pa_size'];
							$taxonomy = 'pa_size';
							$meta = get_post_meta($value['variation_id'], 'attribute_'.$taxonomy, true);
							$term = get_term_by('slug', $meta, $taxonomy);
							$currentTermName= $term->name;
							if ($currentTermName !== '') {
								if( $value['dimensions_html'] !== 'N/A' && $value['weight_html'] !== 'N/A' ) {
									$dimensionsContent.= '<tr><th rowspan="3" class="variation_name">'.$currentTermName.'</th></tr>';
								} else {
									$dimensionsContent.= '<tr><th rowspan="2" class="variation_name">'.$currentTermName.'</th></tr>';
								}
							}
							if ( $value['dimensions_html'] !== 'N/A') {
								$dimensionsContent.= '<tr><td class="woocommerce-product-attributes-item__label">Dimensions</td>';
								$dimensionsContent.= '<td class="woocommerce-product-attributes-item__value">'.$value['dimensions_html'].'</td></tr>';
							}
							if ( $value['weight_html'] !== 'N/A') {
								$dimensionsContent.= '<tr><td class="woocommerce-product-attributes-item__label">Weight</td>';
								$dimensionsContent.= '<td class="woocommerce-product-attributes-item__value">'.$value['weight_html'].'</td></tr>';
							}
						}
					}
				}
				$dimensionsContent.= '</table>';
			}
			if ($dimensionsContent !== '') {
				$returnContent.=$dimensionsContent;
			}
		}
		wc_get_template( 'single-product/tabs/additional-information.php' );
		echo $returnContent;
	}

	/* Create Dimensions Tab If Additional Information Tab Does Not Exist */

	add_filter( 'woocommerce_product_tabs', 'woo_dimensions_product_tab', 98 );
	function woo_dimensions_product_tab( $tabs ) {
		global $product;
		$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );
		if ( $attributes ) {
			$tabs['additional_information']['callback'] = 'woo_dimensions_tab_content';	// Custom additional_information callback
			return $tabs;
		} else {
			add_filter ( 'woocommerce_product_additional_information_heading', 'custom_product_additional_information_heading' ) ;
				function custom_product_additional_information_heading() {
					return 'Dimensions'; // Change Title
				}
			// Adds the new tab
			$tabs['dimensions_tab'] = array(
				'title' 	=> __( 'Dimensions', 'woocommerce' ),
				'priority' 	=> 20,
				'callback' 	=> 'woo_dimensions_tab_content'
			);
			global $product;
			if ( !$product->has_dimensions() && !$product->has_weight() ) {
				unset( $tabs['dimensions_tab'] );
			} else {
				return $tabs;
			}
		}
	}
}
