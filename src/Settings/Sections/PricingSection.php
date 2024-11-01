<?php

namespace MeowCrew\MembersBasedPricing\Settings\Sections;

use  MeowCrew\MembersBasedPricing\MembersBasedPricingPlugin ;
use  MeowCrew\MembersBasedPricing\Settings\CustomOptions\CheckboxGroupOption ;
use  MeowCrew\MembersBasedPricing\Settings\CustomOptions\DisplayAsOption ;
use  MeowCrew\MembersBasedPricing\Settings\Settings ;
class PricingSection extends AbstractSection
{
    public function getTitle()
    {
        return __( 'Pricing', 'subscribers-members-based-pricing' );
    }
    
    public function getDescription()
    {
        return __( 'Here you can decide when the purchase of product will be considered and how to display prices for users with the appropriate product purchased.', 'subscribers-members-based-pricing' );
    }
    
    public function getName()
    {
        return 'pricing_section';
    }
    
    public function getSettings()
    {
        $settings = array( array(
            'title'   => __( 'Display as', 'subscribers-members-based-pricing' ),
            'id'      => Settings::SETTINGS_PREFIX . 'percentage_based_pricing_rule_behaviour',
            'default' => 'full_price',
            'type'    => DisplayAsOption::FIELD_TYPE,
            'options' => array(
            'full_price' => array(
            'label'      => __( 'Exact set price', 'subscribers-members-based-pricing' ),
            'is_premium' => false,
        ),
            'sale_price' => array(
            'label'      => __( 'Sale price', 'subscribers-members-based-pricing' ),
            'is_premium' => true,
        ),
        ),
        ), array(
            'title'      => __( 'Recognize purchase when order status is', 'subscribers-members-based-pricing' ),
            'id'         => Settings::SETTINGS_PREFIX . 'enabled_order_statuses',
            'default'    => array( 'wc-processing', 'wc-completed', 'wc-pending' ),
            'type'       => CheckboxGroupOption::FIELD_TYPE,
            'options'    => wc_get_order_statuses(),
            'is_premium' => true,
        ) );
        if ( MembersBasedPricingPlugin::isSubscriptionsPluginEnabled() ) {
            $settings[] = array(
                'title'      => __( 'Recognize subscription active when its status is', 'subscribers-members-based-pricing' ),
                'id'         => Settings::SETTINGS_PREFIX . 'enabled_subscription_statuses',
                'default'    => array( 'wc-active', 'wc-pending' ),
                'type'       => CheckboxGroupOption::FIELD_TYPE,
                'options'    => wcs_get_subscription_statuses(),
                'is_premium' => true,
            );
        }
        return $settings;
    }
    
    public static function getEnabledOrderStatuses()
    {
        $default = array( 'wc-processing', 'wc-completed', 'wc-pending' );
        return $default;
    }
    
    public static function getEnabledSubscriptionStatuses()
    {
        $default = array( 'wc-active', 'wc-pending' );
        return $default;
    }

}