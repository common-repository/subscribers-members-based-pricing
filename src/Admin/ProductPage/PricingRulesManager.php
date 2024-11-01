<?php namespace MeowCrew\MembersBasedPricing\Admin\ProductPage;

use Exception;
use MeowCrew\MembersBasedPricing\Core\Logger;
use MeowCrew\MembersBasedPricing\Entity\PricingRule;

class PricingRulesManager {

	const PRODUCT_MEMBER_BASED_PRICING_RULES_KEY = '_member_based_pricing_rules';

	/**
	 * Get pricing rule related to a product
	 *
	 * @param  int  $productId
	 * @param  bool  $filterValidPricing
	 *
	 * @return PricingRule[]
	 */
	public static function getProductPricingRules( $productId, $filterValidPricing = true ) {

		$key = self::PRODUCT_MEMBER_BASED_PRICING_RULES_KEY;

		$rules = get_post_meta( $productId, $key, true );

		$pricingRules = array();

		if ( ! empty( $rules ) && is_array( $rules ) ) {

			foreach ( $rules as $identifier => $rule ) {
				try {
					$pricingRule = PricingRule::fromArray( $rule, $productId );

					// Skip pricing rules with invalid pricing
					if ( $filterValidPricing && ! $pricingRule->isValidPricing() ) {
						continue;
					}

					$pricingRules[ $identifier ] = $pricingRule;

				} catch ( Exception $e ) {
					$logger = new Logger();

					$logger->log( $e->getMessage(), Logger::ERROR__LEVEL );
				}
			}
		}

		return apply_filters( 'member_based_pricing/pricing_rules/pricing_rules', $pricingRules, $productId,
			$filterValidPricing );
	}

	public static function updateProductPricingRules( $productId, array $rules ) {

		$data = array();

		foreach ( $rules as $rule ) {
			if ( $rule instanceof PricingRule ) {
				$data[] = $rule->asArray();
			}
		}

		update_post_meta( $productId, self::PRODUCT_MEMBER_BASED_PRICING_RULES_KEY, $data );
	}
}
