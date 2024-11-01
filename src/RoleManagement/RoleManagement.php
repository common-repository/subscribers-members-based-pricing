<?php namespace MeowCrew\MembersBasedPricing\RoleManagement;

use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;

class RoleManagement {

	use ServiceContainerTrait;

	public function __construct() {
		new RoleManagementPage();
	}

	public static function getStandardRoles() {
		return apply_filters( 'member_based_pricing/role_management/default_roles', array(
			'administrator',
			'editor',
			'author',
			'contributor',
			'subscriber',
			'customer',
			'shop_manager',
		) );
	}
}
