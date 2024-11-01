<?php

namespace MeowCrew\MembersBasedPricing\Services;

use  MeowCrew\MembersBasedPricing\PricingRulesDispatcher ;
use  WC_Product_Simple ;
class QuantityManagementService
{
    public function __construct()
    {
    }
    
    public function getProductCartQuantity( $product_id )
    {
        $qty = 0;
        if ( is_array( wc()->cart->cart_contents ) ) {
            foreach ( wc()->cart->cart_contents as $cart_content ) {
                if ( $cart_content['product_id'] == $product_id ) {
                    $qty += $cart_content['quantity'];
                }
            }
        }
        return $qty;
    }

}