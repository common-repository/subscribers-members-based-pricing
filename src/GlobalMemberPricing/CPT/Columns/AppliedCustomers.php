<?php namespace MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\Columns;

use Exception;
use MeowCrew\MembersBasedPricing\Entity\GlobalPricingRule;
use MeowCrew\MembersBasedPricing\Utils\Formatter;
use WC_Customer;

class AppliedCustomers {

	public function getName() {
		return __( 'Customers', 'subscribers-members-based-pricing' );
	}

	public function render( GlobalPricingRule $rule ) {

		$customersMoreThanCanBeShown = count( $rule->getIncludedUsers() ) > 10;

		$appliedCustomersIds = array_slice( $rule->getIncludedUsers(), 0, 10 );
		$appliedRoles        = $rule->getIncludedUserRoles();

		$appliedCustomers = array_filter( array_map( function ( $customerId ) {
			try {
				return new WC_Customer( $customerId );
			} catch ( Exception $e ) {
				return false;
			}
		}, $appliedCustomersIds ) );

		if ( ! empty( $appliedRoles ) ) {

			esc_html_e( 'Roles: ', 'subscribers-members-based-pricing' );

			$appliedRolesString = array_map( function ( $role ) {
				return sprintf( '<span>%s</span>', Formatter::formatRoleString( $role ) );
			}, $appliedRoles );

			echo wp_kses_post( implode( ', ', $appliedRolesString ) . '<br><br>' );
		}


		if ( ! empty( $appliedCustomers ) ) {

			esc_html_e( 'Customers: ', 'subscribers-members-based-pricing' );

			$appliedCustomersString = array_map( function ( WC_Customer $customer ) {
				return Formatter::formatCustomerString( $customer, true );
			}, $appliedCustomers );

			echo wp_kses_post( implode( ', ', $appliedCustomersString ) . ( $customersMoreThanCanBeShown ? '<span> ...</span>' : '' ) );
		}

		if ( empty( $appliedRoles ) && empty( $appliedCustomers ) ) {
			?>
			<b style="color:#d63638"><?php esc_html_e( 'Applied to every user', 'subscribers-members-based-pricing' ); ?></b>
			<?php
		}
	}
}
