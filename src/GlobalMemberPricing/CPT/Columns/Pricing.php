<?php namespace MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns;

use Exception;
use MeowCrew\MembersBasedPricing\Entity\GlobalPricingRule;

class Pricing {

	public function getName() {
		return __( 'Pricing', 'subscribers-members-based-pricing' );
	}

	public function render( GlobalPricingRule $rule ) {
		try {
			$rule->validatePricing();

			if ( $rule->getPriceType() === 'flat' ) {

				if ( $rule->getRegularPrice() ) {
					echo wp_kses_post( sprintf( '<span>%s: <b>%s</b></span>', __( 'Regular price', 'subscribers-members-based-pricing' ), wc_price( $rule->getRegularPrice() ) ) );
				}

				if ( $rule->getSalePrice() ) {
					echo wp_kses_post( sprintf( '<div><span>%s: <b>%s</b></span></div>', __( 'Sale price', 'subscribers-members-based-pricing' ), wc_price( $rule->getSalePrice() ) ) );
				}

			} else {
				if ( $rule->getDiscount() ) {
					echo wp_kses_post( sprintf( '%s: <b>%s</b>', __( 'Discount', 'subscribers-members-based-pricing' ), $rule->getDiscount() . '%' ) );
				}
			}

		} catch ( Exception $e ) {
			echo wp_kses_post( '<div class="help_tip mbp-rule-status mbp-rule-status--invalid" data-tip="' . $e->getMessage() . '">!</div>' );
		}
	}
}
