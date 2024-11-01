<?php namespace MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns;

use MeowCrew\MembersBasedPricing\Entity\GlobalPricingRule;

class AppliedQuantityRules {

	public function getName() {
		return __( 'Quantity rules', 'subscribers-members-based-pricing' );
	}

	public function render( GlobalPricingRule $rule ) {

		$notSetLabel = __( 'Not set', 'subscribers-members-based-pricing' );

		$min     = $rule->getMinimum() ? $rule->getMinimum() : $notSetLabel;
		$max     = $rule->getMaximum() ? $rule->getMaximum() : $notSetLabel;
		$groupOf = $rule->getGroupOf() ? $rule->getGroupOf() : $notSetLabel;

		// translators: %s: minimum amount
		echo esc_html( sprintf( __( 'Minimum: %s', 'subscribers-members-based-pricing' ), $min ) ) . '<br>';
		// translators: %s: maximum amount
		echo esc_html( sprintf( __( 'Maximum: %s', 'subscribers-members-based-pricing' ), $max ) ) . '<br>';
		// translators: %s: quantity step
		echo esc_html( sprintf( __( 'Quantity step: %s', 'subscribers-members-based-pricing' ), $groupOf ) );
	}
}
