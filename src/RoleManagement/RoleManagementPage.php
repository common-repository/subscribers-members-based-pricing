<?php namespace MeowCrew\MembersBasedPricing\RoleManagement;

use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;
use MeowCrew\MembersBasedPricing\RoleManagement\Actions\DeleteRoleAction;
use MeowCrew\MembersBasedPricing\RoleManagement\Actions\NewRoleAction;

class RoleManagementPage {

	use ServiceContainerTrait;

	const PAGE_SLUG = 'mbp_role_management';

	/**
	 * DeleteRoleAction
	 *
	 * @var DeleteRoleAction
	 */
	private $deleteAction;

	/**
	 * NewRoleAction
	 *
	 * @var NewRoleAction
	 */
	private $newRoleAction;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'registerPage' ) );
		$this->deleteAction  = new DeleteRoleAction();
		$this->newRoleAction = new NewRoleAction();
	}

	public function registerPage() {
		add_submenu_page(
			'users.php',
			__( 'Roles Management', 'subscribers-members-based-pricing' ),
			__( 'Roles Management', 'subscribers-members-based-pricing' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'renderPage' )
		);
	}

	public function renderPage() {
		global $wp_roles;

		$rolesTable = new RolesTable( array(), $this->deleteAction );

		$rolesTable->prepare_items();

		$this->getContainer()->getFileManager()->includeTemplate( 'admin/role-management/list.php', array(
			'roles'           => $wp_roles->roles,
			'roles_table'     => $rolesTable,
			'new_role_action' => $this->newRoleAction
		) );
	}

}
