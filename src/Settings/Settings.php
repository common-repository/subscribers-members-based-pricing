<?php namespace MeowCrew\MembersBasedPricing\Settings;

use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;

use MeowCrew\MembersBasedPricing\Settings\CustomOptions\CheckboxGroupOption;
use MeowCrew\MembersBasedPricing\Settings\CustomOptions\CustomizerButtonOption;
use MeowCrew\MembersBasedPricing\Settings\CustomOptions\DisplayAsOption;
use MeowCrew\MembersBasedPricing\Settings\CustomOptions\DurationNumberOption;
use MeowCrew\MembersBasedPricing\Settings\CustomOptions\TemplateOption;
use MeowCrew\MembersBasedPricing\Settings\CustomOptions\SwitchCheckboxOption;

use MeowCrew\MembersBasedPricing\Settings\Sections\AbstractSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\CustomizerSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\DebugSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\DisplayPagesSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\EventsListLinkSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\EventsSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\ExcludedRolesSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\MainSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\MobileSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\PopupBehaviourSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\PopupTriggerSection;
use MeowCrew\MembersBasedPricing\Settings\Sections\PricingSection;

/**
 * Class Settings
 *
 * @package Settings
 */
class Settings {
	
	use ServiceContainerTrait;
	
	const SETTINGS_PREFIX = 'members_and_subscribers_based_pricing_';
	
	const SETTINGS_PAGE = 'members_and_subscribers_based_pricing_settings';
	
	/**
	 * Array with the settings
	 *
	 * @var array
	 */
	private $settings;
	
	/**
	 * Sections
	 *
	 * @var AbstractSection[]
	 */
	private $sections;
	
	/**
	 * Settings constructor.
	 */
	public function __construct() {
		$this->initCustomOptions();
		$this->initSections();
		
		$this->hooks();
		
		foreach ( array( 'enabled_subscription_statuses', 'enabled_order_statuses' ) as $option ) {
			add_action( 'woocommerce_admin_settings_sanitize_option_' . Settings::SETTINGS_PREFIX . $option,
				function ( $value ) {
					
					if ( ! is_array( $value ) ) {
						return array();
					}

					return array_values( $value );
				}, 3, 10 );
		}
	}
	
	public function initCustomOptions() {
		$this->getContainer()->add( 'settings.SwitchCheckboxOption', new SwitchCheckboxOption() );
		$this->getContainer()->add( 'settings.TemplateOption', new TemplateOption() );
		$this->getContainer()->add( 'settings.CheckboxGroupOption', new CheckboxGroupOption() );
		$this->getContainer()->add( 'settings.DisplayAsOption', new DisplayAsOption() );
	}
	
	public function initSections() {
		
		$this->sections = array(
			'main'    => new MainSection(),
			'pricing' => new PricingSection(),
		);
	}
	
	/**
	 * Handle updating settings
	 */
	public function updateSettings() {
		woocommerce_update_options( $this->settings );
	}
	
	/**
	 * Init all settings
	 */
	public function initSettings() {
		
		$settings = array();
		
		foreach ( $this->sections as $section ) {
			$settings[ $section->getName() . '__section' ] = array(
				'title' => $section->getTitle(),
				'desc'  => $section->getDescription(),
				'id'    => self::SETTINGS_PREFIX . $section->getName() . '__section',
				'type'  => 'title',
			);
			
			foreach ( $section->getSettings() as $key => $value ) {
				$settings[ $key ] = $value;
			}
			
			$settings[ $section->getName() . '__section_end' ] = array(
				'id'   => self::SETTINGS_PREFIX . $section->getName() . '__section_end',
				'type' => 'sectionend',
			);
		}
		
		$this->settings = apply_filters( 'member_based_pricing/settings/settings', $settings );
	}
	
	/**
	 * Register hooks
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'initSettings' ) );
		
		add_filter( 'woocommerce_settings_tabs_' . self::SETTINGS_PAGE, array( $this, 'registerSettings' ) );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'addSettingsTab' ), 50 );
		add_action( 'woocommerce_update_options_' . self::SETTINGS_PAGE, array( $this, 'updateSettings' ) );
	}
	
	/**
	 * Add own settings tab
	 *
	 * @param  array  $settings_tabs
	 *
	 * @return mixed
	 */
	public function addSettingsTab( $settings_tabs ) {
		
		$settings_tabs[ self::SETTINGS_PAGE ] = __( 'Members Based Pricing', 'subscribers-members-based-pricing' );
		
		return $settings_tabs;
	}
	
	/**
	 * Add settings to WooCommerce
	 */
	public function registerSettings() {
		woocommerce_admin_fields( $this->settings );
	}
	
	/**
	 * Get setting by name
	 *
	 * @param  string  $option_name
	 * @param  mixed  $default
	 *
	 * @return mixed
	 */
	public function get( $option_name, $default = null ) {
		return get_option( self::SETTINGS_PREFIX . $option_name, $default );
	}
	
	public function doPreventPurchaseForNonLoggedInUsers() {
		return $this->get( 'prevent_purchase_for_non_logged_in_users', 'no' ) === 'yes';
	}
	
	public function doHidePricesForNonLoggedInUsers() {
		return $this->get( 'hide_prices_for_non_logged_in_users', 'no' ) === 'yes';
	}
	
	public function getNonLoggedInUsersPurchaseMessage() {
		// translators: %s: login page url
		return $this->get( 'non_logged_in_users_purchase_message', // translators: %s: login page url
			sprintf( __( 'Please enter %s to make a purchase', 'subscribers-members-based-pricing' ),
				// translators: %s: link
				sprintf( '<a href="%s">%s</a>', wc_get_account_endpoint_url( 'dashboard' ),
					__( 'your account', 'subscribers-members-based-pricing' ) ) ) );
	}
	
	public function getNonLoggedInUsersAddToCartButtonLabel() {
		return $this->get( 'add_to_cart_label_for_non_logged_in_users',
			__( 'Only for registered clients', 'subscribers-members-based-pricing' ) );
	}
	
	public function isDebugEnabled() {
		return $this->get( 'debug', 'no' ) === 'yes';
	}
	
	public function getPercentageBasedRulesBehavior() {
		return $this->get( 'percentage_based_pricing_rule_behaviour', 'full_price' );
	}
	
	/**
	 * Get url to settings page
	 *
	 * @return string
	 */
	public function getLink() {
		return admin_url( 'admin.php?page=wc-settings&tab=' . self::SETTINGS_PAGE );
	}
	
	public function isSettingsPage() {
		return isset( $_GET['tab'] ) && self::SETTINGS_PAGE === $_GET['tab'];
	}
}
