<?php

/**
 * Plugin Name:       Subscribers & Members Based Pricing
 * Plugin URI:        https://meow-crew.com/plugin/subscribers-members-special-pricing
 * Description:       Offer exclusive discounts and personalized pricing to users who purchased certain products or signed to subscription plan.
 * Version:           1.0.0
 * Author:            Meow Crew
 * Author URI:        https://meow-crew.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       subscribers-members-based-pricing
 * Domain Path:       /languages
 *
 * WC requires at least: 4.0
 * WC tested up to: 8.6
 *
 */
use  MeowCrew\MembersBasedPricing\MembersBasedPricingPlugin ;
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

if ( function_exists( 'meowcrew_membersbasedpricing_samspfw_fs' ) ) {
    meowcrew_membersbasedpricing_samspfw_fs()->set_basename( false, __FILE__ );
    return;
} else {
    call_user_func( function () {
        require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
        meowcrew_membersbasedpricing_initFreemius();
        $main = new MembersBasedPricingPlugin( __FILE__ );
        register_activation_hook( __FILE__, array( $main, 'activate' ) );
        $main->run();
    } );
}

function meowcrew_membersbasedpricing_initFreemius()
{
    // Create a helper function for easy SDK access.
    function meowcrew_membersbasedpricing_samspfw_fs()
    {
        global  $meowcrew_membersbasedpricing_samspfw_fs ;
        
        if ( !isset( $meowcrew_membersbasedpricing_samspfw_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $meowcrew_membersbasedpricing_samspfw_fs = fs_dynamic_init( array(
                'id'             => '13546',
                'slug'           => 'subscribers-members-based-pricing',
                'type'           => 'plugin',
                'public_key'     => 'pk_99b8f31761c7b5db9e4f8d39b008d',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 7,
                'is_require_payment' => true,
            ),
                'menu'           => array(
                'first-path' => 'plugins.php',
                'contact'    => false,
                'support'    => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $meowcrew_membersbasedpricing_samspfw_fs;
    }
    
    // Init Freemius.
    meowcrew_membersbasedpricing_samspfw_fs();
    // Signal that SDK was initiated.
    do_action( 'meowcrew_membersbasedpricing_samspfw_fs_loaded' );
}

function meowcrew_membersbasedpricing_samspfw_fs_activation_url()
{
    return ( meowcrew_membersbasedpricing_samspfw_fs()->is_activation_mode() ? meowcrew_membersbasedpricing_samspfw_fs()->get_activation_url() : meowcrew_membersbasedpricing_samspfw_fs()->get_upgrade_url() );
}
