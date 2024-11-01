<?php namespace MeowCrew\MembersBasedPricing\Settings\Sections;

use MeowCrew\MembersBasedPricing\Settings\CustomOptions\TemplateOption;
use MeowCrew\MembersBasedPricing\Settings\CustomOptions\SwitchCheckboxOption;
use MeowCrew\MembersBasedPricing\Settings\Settings;

class MainSection extends AbstractSection {

	public function getTitle() {
		return __( 'Members Based Pricing', 'subscribers-members-based-pricing' );
	}

	public function getDescription() {
		return __( 'You can set up different scenarios for how the plugin should handle unauthorized users here.', 'subscribers-members-based-pricing' );
	}

	public function getName() {
		return 'main_section';
	}

	public function getSettings() {
		return array(
			'prevent_purchase_for_non_logged_in_users' => array(
				'title'   => __( 'Prevent purchase for non-logged-in user', 'subscribers-members-based-pricing' ),
				'id'      => Settings::SETTINGS_PREFIX . 'prevent_purchase_for_non_logged_in_users',
				'default' => 'no',
				'desc'    => __( 'When the user isn\'t logged in, they will not be able to make a purchase', 'subscribers-members-based-pricing' ),
				'type'    => SwitchCheckboxOption::FIELD_TYPE,
			),

			'non_logged_in_users_purchase_message' => array(
				'title'   => __( 'Error message when the non-logged-in user adds to cart', 'subscribers-members-based-pricing' ),
				'id'      => Settings::SETTINGS_PREFIX . 'non_logged_in_users_purchase_message',
				'type'    => TemplateOption::FIELD_TYPE,
				// translators: %s: login page url
				'default' => sprintf( __( 'Please enter %s to make a purchase', 'subscribers-members-based-pricing' ), sprintf( '<a href="%s">%s</a>', wc_get_account_endpoint_url( 'dashboard' ), __( 'your account', 'subscribers-members-based-pricing' ) ) ),
			),

			'hide_prices_for_non_logged_in_users' => array(
				'title'   => __( 'Hide prices for the non-logged-in user', 'subscribers-members-based-pricing' ),
				'id'      => Settings::SETTINGS_PREFIX . 'hide_prices_for_non_logged_in_users',
				'default' => 'no',
				'desc'    => __( 'Show all prices in the store only for the logged-in user', 'subscribers-members-based-pricing' ),
				'type'    => SwitchCheckboxOption::FIELD_TYPE,
			),

			'add_to_cart_label_for_non_logged_in_users' => array(
				'title'       => __( 'Add to cart label for the non-logged-in user', 'subscribers-members-based-pricing' ),
				'desc'        => __( 'Change default Add to cart label to something else to be displayed for the non-logged-in user', 'subscribers-members-based-pricing' ),
				'id'          => Settings::SETTINGS_PREFIX . 'add_to_cart_label_for_non_logged_in_users',
				'default'     => '',
				'placeholder' => __( 'Leave it empty to keep as it is', 'subscribers-members-based-pricing' ),
				'type'        => 'text',
			),
		);
	}

}
