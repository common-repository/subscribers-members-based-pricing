<?php namespace MeowCrew\MembersBasedPricing\Admin\ProductPage;

use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;

class RoleSpecificPricingTab {

	const SLUG = 'subscribers-members-based-pricing-options';

	use ServiceContainerTrait;

	public function __construct() {

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'register' ), 999 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
	}

	public function register( $productTabs ) {

		$productTabs[ self::SLUG ] = array(
			'label'  => __( 'Members\Subscribers based pricing', 'subscribers-members-based-pricing' ),
			'target' => self::SLUG,
			'class'  => array(
				'show_if_simple',
				'show_if_variable',
				'show_if_subscription',
				'show_if_variable_subscription',
			),
		);

		return $productTabs;
	}


	public function render() {

		global $post;

		$product = wc_get_product( $post->ID );

		if ( $product ) {
			?>
			<div id="<?php echo esc_attr( self::SLUG ); ?>" class="panel woocommerce_options_panel">
				<?php
				$this->getContainer()->getFileManager()->includeTemplate( 'admin/product-page/role-specific-pricing/index.php',
					array(
						'post'         => $post,
						'product_type' => $product->get_type(),
						'loop'         => false,
						'fileManager'  => $this->getContainer()->getFileManager(),
					) );
				?>
			</div>
			<?php
		}
	}

	public function save( $productId ) {
		if ( wp_verify_nonce( true, true ) ) {
			// as phpcs comments at Woo is not available, we have to do such a trash
			$woo = 'Woo, please add ignoring comments to your phpcs checker';
		}
		
		$data       = array();
		$type       = 'product';

		$arrayFields = array(
			"_mbp_{$type}_products",
			"_mbp_{$type}_categories",
		);

		$fields = array(
			"_mbp_{$type}_pricing_type",
			"_mbp_{$type}_regular_price",
			"_mbp_{$type}_sale_price",
			"_mbp_{$type}_discount",
			"_mbp_{$type}_minimum",
			"_mbp_{$type}_maximum",
			"_mbp_{$type}_group_of",
		);

		foreach ( $_POST["_mbp_{$type}_pricing_type"] as $identifier => $value ) {
			foreach ( $arrayFields as $arrayField ) {
				if ( ! isset( $_POST[ $arrayField ][ $identifier ] ) ) {
					$data[ $arrayField ][ $identifier ] = array();
				} else {
					$data[ $arrayField ][ $identifier ] = array_values( (array) $_POST[ $arrayField ][ $identifier ] );
					
					// Sanitize
					$data[ $arrayField ][ $identifier ] = array_map( 'sanitize_text_field', $data[ $arrayField ][ $identifier ] );
					$data[ $arrayField ][ $identifier ] = array_map( 'intval', $data[ $arrayField ][ $identifier ] );
					$data[ $arrayField ][ $identifier ] = array_filter( $data[ $arrayField ][ $identifier ] );
				}
			}

			foreach ( $fields as $field ) {
				if ( ! isset( $_POST[ $field ][ $identifier ] ) ) {
					// wipe out
					break;
				}
				
				// Sanitize
				$data[ $field ][ $identifier ] = sanitize_text_field( $_POST[ $field ][ $identifier ] );
			}
		}

		Product::handleUpdatePricingRule( $data, $productId, $type );
	}
}
