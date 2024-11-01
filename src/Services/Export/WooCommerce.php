<?php namespace MeowCrew\MembersBasedPricing\Services\Export;

use MeowCrew\MembersBasedPricing\Admin\ProductPage\PricingRulesManager;
use MeowCrew\MembersBasedPricing\Utils\Strings;
use WC_Product;

/**
 * Class WooCommerce Export
 */
class WooCommerce {

	public $roleBasedRules = array();
	public $customerBasedRules = array();

	/**
	 * Export constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_export_column_names', array( $this, 'addExportColumns' ), 1, 10 );
		add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'addExportColumns' ), 1, 10 );

		foreach ( $this->getPluginColumns() as $key => $name ) {
			add_filter( 'woocommerce_product_export_product_column_' . $key,
				array( $this, 'exportColumnDispatcher' ), 10, 3 );
		}

	}

	public function exportColumnDispatcher( $value, WC_Product $product, $column ) {
		$rules = array();

		if ( Strings::startsWith( $column, 'mbp_role_based_' ) ) {
			$rules = $this->getPricingRules( $product, 'role' );
		} else if ( Strings::startsWith( $column, 'mbp_customer_based_' ) ) {
			$rules = $this->getPricingRules( $product, 'customer' );
		}

		if ( ! empty( $rules ) ) {
			// Pricing type
			if ( Strings::endsWith( $column, 'pricing_type' ) ) {

				$pricingTypeExportString = array();

				foreach ( $rules as $identifier => $rule ) {
					$pricingTypeExportString[] = $identifier . ':' . $rule->getPriceType();
				}

				return implode( ';', $pricingTypeExportString );
			}

			// Regular price
			if ( Strings::endsWith( $column, 'regular_price' ) ) {

				$regularPriceExportString = array();

				foreach ( $rules as $identifier => $rule ) {
					$regularPriceExportString[] = $identifier . ':' . $rule->getRegularPrice();
				}

				return implode( ';', $regularPriceExportString );
			}

			// Sale price
			if ( Strings::endsWith( $column, 'sale_price' ) ) {

				$salePriceExportString = array();

				foreach ( $rules as $identifier => $rule ) {
					$salePriceExportString[] = $identifier . ':' . $rule->getSalePrice();
				}

				return implode( ';', $salePriceExportString );
			}

			// Discount price
			if ( Strings::endsWith( $column, 'discount' ) ) {

				$discountExportString = array();

				foreach ( $rules as $identifier => $rule ) {
					$discountExportString[] = $identifier . ':' . $rule->getDiscount();
				}

				return implode( ';', $discountExportString );

			}

			// Minimum order quantity
			if ( Strings::endsWith( $column, 'minimum_quantity' ) ) {

				$minimumExportString = array();

				foreach ( $rules as $identifier => $rule ) {
					$minimumExportString[] = $identifier . ':' . $rule->getMinimum();
				}

				return implode( ';', $minimumExportString );

			}

			// Maximum order quantity
			if ( Strings::endsWith( $column, 'maximum_quantity' ) ) {

				$maximumExportString = array();

				foreach ( $rules as $identifier => $rule ) {
					$maximumExportString[] = $identifier . ':' . $rule->getMaximum();
				}

				return implode( ';', $maximumExportString );

			}

			// Quantity step
			if ( Strings::endsWith( $column, 'quantity_step' ) ) {

				$stepOfExportString = array();

				foreach ( $rules as $identifier => $rule ) {
					$stepOfExportString[] = $identifier . ':' . $rule->getGroupOf();
				}

				return implode( ';', $stepOfExportString );

			}
		}

		return $value;
	}

	public function getPricingRules( WC_Product $product, $basedOn = 'role' ) {
		if ( 'role' === $basedOn ) {
			if ( ! isset( $this->roleBasedRules[ $product->get_id() ] ) ) {
				$this->roleBasedRules[ $product->get_id() ] = PricingRulesManager::getProductRoleSpecificPricingRules( $product->get_id() );
			}

			return $this->roleBasedRules[ $product->get_id() ];
		} else if ( 'customer' === $basedOn ) {
			if ( ! isset( $this->customerBasedRules[ $product->get_id() ] ) ) {
				$this->customerBasedRules[ $product->get_id() ] = PricingRulesManager::getProductCustomerSpecificPricingRules( $product->get_id() );
			}

			return $this->customerBasedRules[ $product->get_id() ];
		}

		return array();
	}

	/**
	 * Add export columns
	 *
	 * @param array $columns
	 *
	 * @return array $options
	 */
	public function addExportColumns( $columns ) {
		return array_merge( $columns, $this->getPluginColumns() );
	}

	public function getPluginColumns() {
		$columns = array();

		$columns['mbp_member_based_pricing_type']  = __( 'Role-based pricing type', 'subscribers-members-based-pricing' );
		$columns['mbp_role_based_discount']      = __( 'Role-based discount', 'subscribers-members-based-pricing' );
		$columns['mbp_role_based_regular_price'] = __( 'Role-based regular price', 'subscribers-members-based-pricing' );
		$columns['mbp_role_based_sale_price']    = __( 'Role-based sale price', 'subscribers-members-based-pricing' );

		$columns['mbp_role_based_minimum_quantity'] = __( 'Role-based minimum quantity', 'subscribers-members-based-pricing' );
		$columns['mbp_role_based_maximum_quantity'] = __( 'Role-based maximum quantity', 'subscribers-members-based-pricing' );
		$columns['mbp_role_based_quantity_step']    = __( 'Role-based quantity step', 'subscribers-members-based-pricing' );

		$columns['mbp_customer_based_pricing_type']  = __( 'Customer-based pricing type', 'subscribers-members-based-pricing' );
		$columns['mbp_customer_based_discount']      = __( 'Customer-based discount', 'subscribers-members-based-pricing' );
		$columns['mbp_customer_based_regular_price'] = __( 'Customer-based regular price', 'subscribers-members-based-pricing' );
		$columns['mbp_customer_based_sale_price']    = __( 'Customer-based sale price', 'subscribers-members-based-pricing' );

		$columns['mbp_customer_based_minimum_quantity'] = __( 'Customer-based minimum quantity', 'subscribers-members-based-pricing' );
		$columns['mbp_customer_based_maximum_quantity'] = __( 'Customer-based maximum quantity', 'subscribers-members-based-pricing' );
		$columns['mbp_customer_based_quantity_step']    = __( 'Customer-based quantity step', 'subscribers-members-based-pricing' );

		return $columns;
	}
}
