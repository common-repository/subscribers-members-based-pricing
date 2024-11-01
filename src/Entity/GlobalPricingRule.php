<?php namespace MeowCrew\MembersBasedPricing\Entity;

use Exception;
use WC_Product;
use WP_User;

class GlobalPricingRule extends PricingRule {

	/**
	 * Included categories
	 *
	 * @var array
	 */
	public $includedProductCategories = array();

	/**
	 * Included products
	 *
	 * @var array
	 */
	public $includedProducts = array();

	/**
	 * Included product roles
	 *
	 * @var array
	 */
	public $includedUsersRole = array();

	/**
	 * Included users
	 *
	 * @var array
	 */
	public $includedUsers = array();

	/**
	 * Is suspended
	 *
	 * @var bool
	 */
	private $isSuspended;

	/**
	 * Related product id
	 *
	 * @var int
	 */
	private $appliedProductId;

	public function getRuleId() {
		return parent::getProductId();
	}

	public function setAppliedProductId( $productId ) {
		$this->appliedProductId = $productId;
	}

	public function getProductId() {
		return $this->appliedProductId;
	}

	/**
	 * Get included categories
	 *
	 * @return array
	 */
	public function getIncludedProductCategories() {
		return $this->includedProductCategories;
	}

	/**
	 * Set included categories
	 *
	 * @param  array  $includedProductCategories
	 */
	public function setIncludedProductCategories( array $includedProductCategories ) {
		$this->includedProductCategories = $includedProductCategories;
	}

	/**
	 * Get included products
	 *
	 * @return array
	 */
	public function getIncludedProducts() {
		return $this->includedProducts;
	}

	/**
	 * Set included products
	 *
	 * @param  array  $includedProducts
	 */
	public function setIncludedProducts( array $includedProducts ) {
		$this->includedProducts = $includedProducts;
	}

	/**
	 * Get included user roles
	 *
	 * @return array
	 */
	public function getIncludedUserRoles() {
		return $this->includedUsersRole;
	}

	/**
	 * Set included user roles
	 *
	 * @param  array  $includedUsersRole
	 */
	public function setIncludedUsersRole( array $includedUsersRole ) {
		$this->includedUsersRole = $includedUsersRole;
	}

	/**
	 * Get included users
	 *
	 * @return array
	 */
	public function getIncludedUsers() {
		return $this->includedUsers;
	}

	/**
	 * Set included users
	 *
	 * @param  array  $includedUsers
	 */
	public function setIncludedUsers( array $includedUsers ) {
		$this->includedUsers = $includedUsers;
	}

	public function asArray() {
		return array_merge( parent::asArray(), array(
			'included_categories' => $this->getIncludedProductCategories(),
			'included_products'   => $this->getIncludedProducts(),
			'included_users'      => $this->getIncludedUsers(),
			'included_user_roles' => $this->getIncludedUserRoles(),
			'rule_id'             => $this->getRuleId(),
			'is_suspended'        => $this->isSuspended(),
		) );
	}

	/**
	 * Save global price instance
	 *
	 * @param  GlobalPricingRule  $rule
	 * @param $ruleId
	 *
	 * @throws Exception
	 */
	public static function save( GlobalPricingRule $rule, $ruleId ) {

		$ruleData                 = $rule->asArray();
		$ruleData['is_suspended'] = wc_bool_to_string( $rule->isSuspended() );
		unset( $ruleData['rule_id'] );

		foreach ( $ruleData as $key => $value ) {
			update_post_meta( $ruleId, '_mbp_' . $key, $value );
		}
	}

	public static function build( $ruleId ) {
		$dataToRead = array(
			'_mbp_pricing_type'  => 'pricing_type',
			'_mbp_products'      => 'products',
			'_mbp_categories'    => 'categories',
			'_mbp_regular_price' => 'regular_price',
			'_mbp_sale_price'    => 'sale_price',
			'_mbp_discount'      => 'discount',
			'_mbp_minimum'       => 'minimum',
			'_mbp_maximum'       => 'maximum',
			'_mbp_group_of'      => 'group_of',
			'_mbp_is_suspended'  => 'is_suspended',
		);

		$data = array();

		foreach ( $dataToRead as $key => $name ) {
			$data[ $name ] = get_post_meta( $ruleId, $key, true );
		}

		$priceRule = self::fromArray( $data );

		$existingRoles = wp_roles()->roles;

		$includedCategoriesIds = array_filter( array_map( 'intval',
			(array) get_post_meta( $ruleId, '_mbp_included_categories', true ) ) );
		$includedProductsIds   = array_filter( array_map( 'intval',
			(array) get_post_meta( $ruleId, '_mbp_included_products', true ) ) );

		$includedUsersRole = array_filter( (array) get_post_meta( $ruleId, '_mbp_included_user_roles', true ),
			function ( $role ) use ( $existingRoles ) {
				return array_key_exists( $role, $existingRoles );
			} );

		$includedUsers = array_filter( array_map( 'intval',
			(array) get_post_meta( $ruleId, '_mbp_included_users', true ) ) );

		$isSuspended = get_post_meta( $ruleId, '_mbp_is_suspended', true ) === 'yes';

		$priceRule->setIncludedProductCategories( $includedCategoriesIds );
		$priceRule->setIncludedUsers( $includedUsers );
		$priceRule->setIncludedUsersRole( $includedUsersRole );
		$priceRule->setIncludedProducts( $includedProductsIds );
		$priceRule->setIsSuspended( $isSuspended );

		return $priceRule;
	}

	/**
	 * Set price type
	 *
	 * @override
	 *
	 * @param  string  $priceType
	 */
	public function setPriceType( $priceType ) {
		parent::setPriceType( in_array( $priceType, array( 'percentage', 'flat' ) ) ? $priceType : 'percentage' );
	}

	public function setIsSuspended( $isSuspended ) {
		$this->isSuspended = (bool) $isSuspended;
	}

	public function suspend() {
		$this->setIsSuspended( true );
	}

	public function reactivate() {
		$this->setIsSuspended( false );
	}

	public function isSuspended() {
		return $this->isSuspended;
	}

	/**
	 * Wrapper for the main "match" function to provide the hook for 3rd party devs
	 *
	 * @param  WP_User  $user
	 * @param  WC_Product  $product
	 *
	 * @return mixed|void
	 */
	public function matchRequirements( WP_User $user, WC_Product $product ) {
		$matched = $this->_matchRequirements( $user, $product );

		return apply_filters( 'member_based_pricing/pricing_rule/match_requirements', $matched, $user, $product );
	}

	protected function _matchRequirements( WP_User $user, WC_Product $product ) {

		$parentProduct = $product->is_type( array(
			'variation',
			'subscription-variation',
		) ) ? wc_get_product( $product->get_parent_id() ) : $product;

		$productMatched     = false;
		$productLimitations = false;

		// There are rule limitation for specific products
		if ( ! empty( $this->getIncludedProducts() ) ) {
			$productLimitations = true;

			if ( in_array( $product->get_id(), $this->getIncludedProducts() ) || in_array( $parentProduct->get_id(),
					$this->getIncludedProducts() ) ) {
				$productMatched = true;
			}
		}

		if ( ! empty( $this->getIncludedProductCategories() ) ) {
			$productLimitations = true;

			if ( ! empty( array_intersect( $parentProduct->get_category_ids(),
				$this->getIncludedProductCategories() ) ) ) {
				$productMatched = true;
			}
		}

		// There is product limitation and the product/category does not match the rule
		if ( $productLimitations && ! $productMatched ) {
			return false;
		}

		$userPassed = false;

		// Applied to everyone
		if ( empty( $this->getIncludedUserRoles() ) && empty( $this->getIncludedUsers() ) ) {
			$userPassed = true;
		}

		if ( in_array( $user->ID, $this->getIncludedUsers() ) ) {
			$userPassed = true;
		}

		foreach ( $this->getIncludedUserRoles() as $role ) {
			if ( in_array( $role, $user->roles ) ) {
				$userPassed = true;
			}
		}

		if ( $userPassed ) {
			return $this->isApplicableToUser( $user );
		}

		return false;
	}

}
