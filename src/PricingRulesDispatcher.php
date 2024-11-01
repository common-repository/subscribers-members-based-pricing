<?php namespace MeowCrew\MembersBasedPricing;

use MeowCrew\MembersBasedPricing\Admin\ProductPage\PricingRulesManager;
use \MeowCrew\MembersBasedPricing\Entity\PricingRule;
use MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\MemberBasedPricingCPT;
use WP_User;

class PricingRulesDispatcher {

	/**
	 * Dispatched rules. Used for the cache
	 *
	 * @var PricingRule[]
	 */
	protected static $dispatchedRules = array();

	/**
	 * Wrapper for the main dispatch function to provide the hook for 3rd-party devs
	 *
	 * @param  int  $productId
	 * @param  null  $parentId
	 * @param  null  $user
	 * @param  bool  $validatePricing
	 *
	 * @return false|PricingRule
	 */
	public static function dispatchRule( $productId, $parentId = null, $user = null, $validatePricing = true ) {
		$dispatchedRule = self::_dispatchRule( $productId, $parentId, $user, $validatePricing );

		return apply_filters( 'member_based_pricing/pricing_rules_dispatcher/dispatched_rule', $dispatchedRule,
			$productId, $parentId, $user, $validatePricing, self::$dispatchedRules );
	}


	/**
	 * The main method to get applied rule for a product
	 *
	 * @param  int  $productId
	 * @param  null  $parentId
	 * @param  null  $user
	 * @param  bool  $validatePricing
	 *
	 * @return false|PricingRule
	 */
	protected static function _dispatchRule( $productId, $parentId = null, $user = null, $validatePricing = true ) {

		$cacheKey = $productId;

		// Cache
		if ( array_key_exists( $productId, self::$dispatchedRules ) ) {
			return self::$dispatchedRules[ $cacheKey ];
		}

		$product = wc_get_product( $productId );

		// Works only for specific product types
		if ( ! $product || ! $product->is_type( array(
				'variation',
				'simple',
				'subscription',
				'subscription-variation',
			) ) ) {
			return false;
		}

		$parentId = $parentId ? $parentId : $product->get_parent_id();
		$user     = $user instanceof WP_User ? $user : wp_get_current_user();

		// The rules can be applied to any non-logged in users
		if ( ! $user ) {
			return false;
		}

		$pricingRules = PricingRulesManager::getProductPricingRules( $productId, $validatePricing );
		if ( empty( $pricingRules ) && $product->get_type() === 'variation' ) {
			$pricingRules = PricingRulesManager::getProductPricingRules( $parentId, $validatePricing );
		}

		foreach ( $pricingRules as $rule ) {

			if ( $rule->isApplicableToUser( $user ) ) {
				self::$dispatchedRules[ $cacheKey ] = $rule;
				return $rule;
			}
		}

		$globalRules = MemberBasedPricingCPT::getGlobalRules( $validatePricing );

		foreach ( $globalRules as $rule ) {

			if ( $rule->matchRequirements( $user, $product ) ) {

				$rule->setAppliedProductId( $productId );

				$rule->setOriginalProductPrice( floatval( $product->get_price( 'edit' ) ) );

				self::$dispatchedRules[ $cacheKey ] = $rule;

				return $rule;
			}
		}

		self::$dispatchedRules[ $cacheKey ] = false;

		return false;
	}

}
