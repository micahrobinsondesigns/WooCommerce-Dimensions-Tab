<?php
/**
 * @wordpress-plugin
 * Plugin Name:       woocommerce Dimensions Tab
 * Description:       Display dimensions of variations below description tab.
 * Version:           1.0
 * Author:            Micah Robinson
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-dimensions
 WC tested up to: 3.6
 */

/** Die if accessed directly
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
				$dimensions = $product->get_dimensions();
				if ( ! empty( $dimensions ) ) {
					$returnContent.= '<div class="dimensions">'.$dimensions.'</div>';
				}
			} else if ($product->get_type() == 'variable') {
				$variations = $product->get_available_variations();
				$foundSizes= array();
				$dimensionsContent.= '<table class="shop_dimensions">';
				foreach ( $variations as $key => $value ) {
					if ( $value['dimensions_html'] !== 'N/A') {
						if (!in_array($value['attributes']['attribute_pa_size'], $foundSizes)) {
							$foundSizes[]= $value['attributes']['attribute_pa_size'];

							$taxonomy = 'pa_size';
							$meta = get_post_meta($value['variation_id'], 'attribute_'.$taxonomy, true);
							$term = get_term_by('slug', $meta, $taxonomy);

							$currentTermName= $term->name;
							if ($currentTermName == '') {
								$dimensionsContent.= '<tr><td>'.$value['dimensions_html'].'</td></tr>';
							} else {
								$dimensionsContent.= '<tr><th>'.$currentTermName.'</th>';
								$dimensionsContent.= '<td>'.$value['dimensions_html'].'</td></tr>';
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
		echo $returnContent;
	}

	/* Create Dimensions Tab */

	add_filter( 'woocommerce_product_tabs', 'woo_dimensions_product_tab', 0 );
	function woo_dimensions_product_tab( $tabs ) {

		// Adds the new tab
		$tabs['dimensions_tab'] = array(
			'title' 	=> __( 'Dimensions', 'woocommerce' ),
			'priority' 	=> 30,
			'callback' 	=> 'woo_dimensions_tab_content'
		);
		global $product;
		if ( !$product->has_dimensions() ) {
			unset( $tabs['dimensions_tab'] );
		} else {
			return $tabs;
		}
	}

}
