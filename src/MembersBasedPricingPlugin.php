<?php namespace MeowCrew\MembersBasedPricing;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use MeowCrew\MembersBasedPricing\Admin\Admin;
use MeowCrew\MembersBasedPricing\Core\AdminNotifier;
use MeowCrew\MembersBasedPricing\Core\FileManager;
use MeowCrew\MembersBasedPricing\Core\Logger;
use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;
use MeowCrew\MembersBasedPricing\GlobalMemberPricing\CPT\MemberBasedPricingCPT;
use MeowCrew\MembersBasedPricing\RoleManagement\RoleManagement;
use MeowCrew\MembersBasedPricing\Services\Select2LookupService;
use MeowCrew\MembersBasedPricing\Services\NonLoggedUsersService;
use MeowCrew\MembersBasedPricing\Services\ProductPricingService;
use MeowCrew\MembersBasedPricing\Services\QuantityManagementService;
use MeowCrew\MembersBasedPricing\Settings\Settings;
use MeowCrew\MembersBasedPricing\Services\Import\WooCommerce as WooCommerceImport;
use MeowCrew\MembersBasedPricing\Services\Export\WooCommerce as WooCommerceExport;


/**
 * Class MembersBasedPricingPlugin
 *
 * @package MeowCrew\MembersBasedPricing
 */
class MembersBasedPricingPlugin {
	
	use ServiceContainerTrait;
	
	const VERSION = '1.0.0';
	
	/**
	 * MembersBasedPricingPlugin constructor.
	 *
	 * @param  string  $mainFile
	 */
	public function __construct( $mainFile ) {
		FileManager::init( $mainFile );
		
		add_action( 'plugins_loaded', array( $this, 'loadTextDomain' ) );
		add_action( 'member_based_pricing/container/services_init', array( $this, 'addSettingsLink' ) );
		
		add_action( 'before_woocommerce_init', function () use ( $mainFile ) {
			if ( class_exists( FeaturesUtil::class ) ) {
				FeaturesUtil::declare_compatibility( 'custom_order_tables', $mainFile, true );
			}
		} );
	}
	
	public function addSettingsLink() {
		add_filter( 'plugin_action_links_' . plugin_basename( $this->getContainer()->getFileManager()->getMainFile() ),
			function ( $actions ) {
				$actions[] = '<a href="' . $this->getContainer()->getSettings()->getLink() . '">' . __( 'Settings',
						'subscribers-members-based-pricing' ) . '</a>';
				$actions[] = '<a target="_blank" href="' . self::getDocumentationLink() . '">' . __( 'Documentation',
						'subscribers-members-based-pricing' ) . '</a>';
				
				return $actions;
			}, 10, 4 );
	}
	
	/**
	 * Run plugin part
	 */
	public function run() {
		$this->getContainer()->add( 'fileManager', FileManager::getInstance() );
		$this->getContainer()->add( 'adminNotifier', new AdminNotifier() );
		$this->getContainer()->add( 'logger', new Logger() );
		
		if ( $this->checkRequirements() ) {
			$this->initServices();
		}
	}
	
	public function initServices() {
		
		$this->getContainer()->add( 'settings', new Settings() );
		
		$this->getContainer()->add( 'admin', new Admin() );
		$this->getContainer()->add( 'globalRoleSpecificPricingCPT', new MemberBasedPricingCPT() );
		$this->getContainer()->add( 'roleManagement', new RoleManagement() );
		
		if ( ! is_admin() ) {
			$this->getContainer()->add( 'ProductPricingService', new ProductPricingService() );
			$this->getContainer()->add( 'QuantityManagementService', new QuantityManagementService() );
			$this->getContainer()->add( 'NonLoggedUsersService', new NonLoggedUsersService() );
		}
		
		$this->getContainer()->add( 'Select2LookupService', new Select2LookupService() );
		
		// Import/Export
		$this->getContainer()->add( 'Import.WooCommerce', new WoocommerceImport() );
		$this->getContainer()->add( 'Export.WooCommerce', new WooCommerceExport() );
		
		do_action( 'member_based_pricing/container/services_init' );
	}
	
	/**
	 * Load plugin translations
	 */
	public function loadTextDomain() {
		$name = $this->getContainer()->getFileManager()->getPluginName();
		load_plugin_textdomain( 'subscribers-members-based-pricing', false, $name . '/languages/' );
	}
	
	public function checkRequirements() {
		/* translators: %s = required plugin */
		$message = __( 'Members Based Pricing for WooCommerce requires %s plugin to be active!',
			'subscribers-members-based-pricing' );
		
		$plugins = $this->getRequiredPluginsToBeActive();
		
		if ( count( $plugins ) ) {
			foreach ( $plugins as $plugin ) {
				$error = sprintf( $message, $plugin );
				$this->getContainer()->getAdminNotifier()->push( $error, AdminNotifier::ERROR, false );
			}
			
			return false;
		}
		
		return true;
	}
	
	public function activate() {
		if ( self::isSubscriptionsPluginEnabled() ) {
			set_transient( 'meowcrew_membersbasedpricing_show_subscriptions_plugin_hint', 'yes' );
		}
	}
	
	public function getRequiredPluginsToBeActive() {
		
		$plugins = array();
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
			$plugins[] = 'WooCommerce';
		}
		
		return $plugins;
	}
	
	public static function isSubscriptionsPluginEnabled() {
		return is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) || is_plugin_active_for_network( 'woocommerce-subscriptions/woocommerce-subscriptions.php' );
	}
	
	public static function getDocumentationLink() {
		return 'https://meow-crew.com/documentation/subscribers-members-based-pricing-documentation';
	}
}
