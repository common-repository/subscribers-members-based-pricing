<?php namespace MeowCrew\MembersBasedPricing\Admin;

use MeowCrew\MembersBasedPricing\Admin\ProductPage\Product;
use MeowCrew\MembersBasedPricing\Core\AdminNotifier;
use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;
use MeowCrew\MembersBasedPricing\MembersBasedPricingPlugin;
use MeowCrew\MembersBasedPricing\RoleSpecificPricingPlugin;

class Admin {

	use ServiceContainerTrait;

	/**
	 * Admin constructor.
	 */
	public function __construct() {

		new Product();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );

		add_action( 'init', function () {

			if ( get_transient( 'meowcrew_membersbasedpricing_show_subscriptions_plugin_hint', 'no' ) !== 'yes' ) {
				return;
			}
			// translators: %s: url, %s: "Settings"
			$settingsLink = sprintf( '<a href="%s">%s</a>', $this->getContainer()->getSettings()->getLink(),
				__( 'settings', 'subscribers-members-based-pricing' ) );

			// translators: %s: link
			$message = sprintf( __( 'To create special pricing for subscribers, choose a state of subscription first in %s',
				'subscribers-members-based-pricing' ), $settingsLink );

			$this->getContainer()->getAdminNotifier()->push( $message, AdminNotifier::INFO );

			set_transient('meowcrew_membersbasedpricing_show_subscriptions_plugin_hint', 'no');
		} );
	}

	public function enqueueScripts( $page ) {

		wp_enqueue_script( 'members-based-pricing-admin-js',
			$this->getContainer()->getFileManager()->locateAsset( 'admin/main.js' ), array( 'jquery' ),
			MembersBasedPricingPlugin::VERSION );
		wp_enqueue_style( 'members-based-pricing-admin-css',
			$this->getContainer()->getFileManager()->locateAsset( 'admin/style.css' ), array(),
			MembersBasedPricingPlugin::VERSION );

		if ( $this->getContainer()->getSettings()->isSettingsPage() ) {
			wp_enqueue_script( 'members-based-pricing-admin-settings-js',
				$this->getContainer()->getFileManager()->locateAsset( 'admin/settings.js' ), array( 'jquery' ),
				MembersBasedPricingPlugin::VERSION );
		}
	}
}
