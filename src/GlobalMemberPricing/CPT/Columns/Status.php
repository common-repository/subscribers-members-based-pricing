<?php namespace MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns;

use Exception;
use MeowCrew\MembersBasedPricing\Entity\GlobalPricingRule;

class Status {

	public function getName() {
		return __( 'Status', 'subscribers-members-based-pricing' );
	}

	public function render( GlobalPricingRule $rule ) {
		if ( $rule->isSuspended() ) {
			?>
			<mark class="mbp-rule-suspend-status mbp-rule-suspend-status--suspended">
				<span><?php esc_html_e( 'Suspended', 'subscribers-members-based-pricing' ); ?></span>
			</mark>
			<?php
		} else {
			?>
			<mark class="mbp-rule-suspend-status mbp-rule-suspend-status--active">
				<span><?php esc_html_e( 'Active', 'subscribers-members-based-pricing' ); ?></span>
			</mark>
			<?php
		}
	}
}
