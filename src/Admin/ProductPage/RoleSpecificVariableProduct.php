<?php namespace MeowCrew\MembersBasedPricing\Admin\ProductPage;

use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;
use WP_Post;

class RoleSpecificVariableProduct {
	
	use ServiceContainerTrait;
	
	public function __construct() {
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'renderPriceRules' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'updatePriceRules' ), 10, 3 );
	}
	
	/**
	 * Update price quantity rules for variation product
	 *
	 * @param  int  $variation_id
	 * @param  int  $loop
	 */
	public function updatePriceRules( $variation_id, $loop ) {
		
		check_ajax_referer( 'save-variations', 'security' );
		
		$data       = array();
		$_data      = array();
		
		$arrayFields = array(
			'_mbp_product_products_variation',
			'_mbp_product_categories_variation',
		);
		
		$fields = array(
			'_mbp_product_pricing_type_variation',
			'_mbp_product_regular_price_variation',
			'_mbp_product_sale_price_variation',
			'_mbp_product_discount_variation',
			'_mbp_product_minimum_variation',
			'_mbp_product_maximum_variation',
			'_mbp_product_group_of_variation',
		);
		
		foreach ( $_POST['_mbp_product_pricing_type_variation'][ $loop ] as $identifier => $value ) {
			foreach ( $arrayFields as $arrayField ) {
				if ( ! isset( $_POST[ $arrayField ][ $loop ][ $identifier ] ) ) {
					$data[ $arrayField ][ $identifier ] = array();
				} else {
					$data[ $arrayField ][ $identifier ] = array_values( (array) $_POST[ $arrayField ][ $loop ][ $identifier ] );
					
					// Sanitize
					$data[ $arrayField ][ $identifier ] = array_map( 'sanitize_text_field', $data[ $arrayField ][ $identifier ] );
					$data[ $arrayField ][ $identifier ] = array_map( 'intval', $data[ $arrayField ][ $identifier ] );
					$data[ $arrayField ][ $identifier ] = array_filter( $data[ $arrayField ][ $identifier ] );
				}
			}
			
			foreach ( $fields as $field ) {
				if ( ! isset( $_POST[ $field ][ $loop ][ $identifier ] ) ) {
					// wipe out
					break;
				}
				// Sanitize
				$data[ $field ][ $identifier ] = sanitize_text_field( $_POST[ $field ][ $loop ][ $identifier ] );
			}
		}
		
		foreach ( $data as $key => $value ) {
			$_data[ str_replace( '_variation', '', $key ) ] = $value;
		}
		
		Product::handleUpdatePricingRule( $_data, $variation_id, 'product' );
	}
	
	/**
	 * Render inputs for price rules on variation
	 *
	 * @param  int  $loop
	 * @param  array  $variation_data
	 * @param  WP_Post  $variation
	 */
	public function renderPriceRules( $loop, $variation_data, WP_Post $variation ) {
		$this->getContainer()->getFileManager()->includeTemplate( 'admin/product-page/role-specific-pricing/index.php',
			array(
				'post'         => $variation,
				'loop'         => $loop,
				'product_type' => 'variation',
				'fileManager'  => $this->getContainer()->getFileManager(),
			) );
	}
}
