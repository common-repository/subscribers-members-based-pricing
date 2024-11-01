<?php namespace MeowCrew\MembersBasedPricing\RoleManagement\Actions;

use Exception;

class NewRoleAction extends RoleManagementPageAction {

	public function handle() {
		$roleName    = $this->getRoleName();
		$inheritRole = $this->getInheritedRole();

		$newCapabilities = array();

		if ( $inheritRole ) {
			$roles = wp_roles()->roles;

			$role = array_key_exists( $inheritRole, $roles ) ? $roles[ $inheritRole ] : false;

			if ( ! empty( $role ) ) {
				$newCapabilities = $role['capabilities'];
			}
		}

		add_role( $roleName, $roleName, $newCapabilities );

		$this->getContainer()->getAdminNotifier()->flash( __( 'The role has been added successfully.', 'subscribers-members-based-pricing' ), 'success', true );

		wp_redirect( wp_get_referer() );
	}

	public function validate() {

		if ( ! $this->getRoleName() ) {
			throw new Exception( esc_html__( 'Role name is required.', 'subscribers-members-based-pricing' ) );
		}

		$roles = wp_roles()->roles;

		if ( $this->getInheritedRole() && ! array_key_exists( $this->getInheritedRole(), $roles ) ) {
			throw new Exception( esc_html__( 'Invalid inherited role.', 'subscribers-members-based-pricing' ) );
		}

		parent::validate();
	}

	public function getRoleName() {
		return isset( $_REQUEST['role_name'] ) ? sanitize_text_field( $_REQUEST['role_name'] ) : false;
	}

	public function getInheritedRole() {
		return isset( $_REQUEST['inherited_role'] ) ? sanitize_text_field( $_REQUEST['inherited_role'] ) : false;
	}

	public function getActionSlug() {
		return 'mbp_new_role__action';
	}
}
