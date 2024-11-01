<?php namespace MeowCrew\MembersBasedPricing\RoleManagement\Actions;

use Exception;
use MeowCrew\MembersBasedPricing\RoleManagement\RoleManagement;

class DeleteRoleAction extends RoleManagementPageAction {

	public function handle() {
		$roleName = $this->getRoleName();

		if ( ! in_array( $roleName, RoleManagement::getStandardRoles() ) ) {
			remove_role( $roleName );

			$this->getContainer()->getAdminNotifier()->flash( __( 'Role deleted successfully.', 'subscribers-members-based-pricing' ) );

		} else {
			$this->getContainer()->getAdminNotifier()->flash( __( 'Standard roles cannot be deleted or modified.', 'subscribers-members-based-pricing' ),
				'error', true );
		}

		wp_redirect( wp_get_referer() );

	}

	public function validate() {

		$roles = wp_roles()->roles;

		if ( ! $this->getRoleName() || ! array_key_exists( $this->getRoleName(), $roles ) ) {
			throw new Exception( esc_html__( 'Invalid role name', 'subscribers-members-based-pricing' ) );
		}

		parent::validate();
	}

	public function getRoleName() {
		return isset( $_REQUEST['role'] ) ? sanitize_text_field( $_REQUEST['role'] ) : false;
	}

	public function getActionSlug() {
		return 'mbp_delete_role__action';
	}
}
