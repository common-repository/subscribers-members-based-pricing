<?php

namespace MeowCrew\MembersBasedPricing\Entity;

use  Exception ;
use  MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait ;
use  MeowCrew\MembersBasedPricing\MembersBasedPricingPlugin ;
use  MeowCrew\MembersBasedPricing\Settings\Sections\PricingSection ;
use  WC_Product ;
use  WP_User ;
class PricingRule
{
    use  ServiceContainerTrait ;
    /**
     * Products needs to be purchased
     *
     * @var array
     */
    private  $products ;
    /**
     * Any products in this categories need to be purchased
     *
     * @var array
     */
    private  $categories ;
    /**
     * Product id
     *
     * @var int
     */
    private  $productId ;
    /**
     * Pricing Type
     *
     * @var string
     */
    private  $priceType ;
    /**
     * Regular price
     *
     * @var float
     */
    private  $regularPrice ;
    /**
     * Sale price
     *
     * @var float
     */
    private  $salePrice ;
    /**
     * Discount
     *
     * @var float
     */
    private  $discount ;
    /**
     * Product minimum purchase quantity
     *
     * @var int
     */
    private  $minimum ;
    /**
     * Product maximum purchase quantity
     *
     * @var int|null
     */
    private  $maximum ;
    /**
     * Group of product quantity
     *
     * @var int|null
     */
    private  $groupOf ;
    /**
     * Original product price
     *
     * @var float
     */
    private  $originalProductPrice ;
    /**
     * PricingRule constructor.
     *
     * @param  string  $priceType
     * @param  string  $regularPrice
     * @param  string  $salePrice
     * @param  int  $discount
     * @param  int  $minimum
     * @param  int  $maximum
     * @param  int  $groupOf
     * @param  null  $productId
     */
    public function __construct(
        $priceType,
        $products = array(),
        $categories = array(),
        $regularPrice = null,
        $salePrice = null,
        $discount = null,
        $minimum = null,
        $maximum = null,
        $groupOf = null,
        $productId = null
    )
    {
        $this->setPriceType( $priceType );
        $this->setProducts( $products );
        $this->setCategories( $categories );
        $this->setRegularPrice( $regularPrice );
        $this->setSalePrice( $salePrice );
        $this->setDiscount( $discount );
        $this->setMinimum( $minimum );
        $this->setMaximum( $maximum );
        $this->setGroupOf( $groupOf );
        $this->setProductId( $productId );
    }
    
    /**
     * Get products
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
    
    /**
     * Set products
     *
     * @param  array  $products
     */
    public function setProducts( array $products )
    {
        $this->products = array_map( 'intval', $products );
    }
    
    /**
     * Get categories
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }
    
    /**
     * Set categories
     *
     * @param  array  $categories
     */
    public function setCategories( array $categories )
    {
        $this->categories = array_map( 'intval', $categories );
    }
    
    public function isPurchasable()
    {
        return $this->getPrice();
    }
    
    /**
     * Set original product price
     *
     * @param $price
     */
    public function setOriginalProductPrice( $price )
    {
        $this->originalProductPrice = $price;
    }
    
    public function getOriginalProductPrice()
    {
        if ( is_null( $this->originalProductPrice ) ) {
            $this->originalProductPrice = $this->getProduct()->get_price( 'edit' );
        }
        return $this->originalProductPrice;
    }
    
    public function getPrice( WC_Product $product = null )
    {
        
        if ( $this->getPriceType() === 'percentage' && meowcrew_membersbasedpricing_samspfw_fs()->is__premium_only() ) {
            $discount = $this->getDiscount();
            $productPrice = $this->getOriginalProductPrice();
            $price = $productPrice * ((100 - $discount) / 100);
        } else {
            $price = $this->getRegularPrice();
            if ( $this->getSalePrice() ) {
                $price = $this->getSalePrice();
            }
        }
        
        return apply_filters( 'member_based_pricing/pricing_rule/price', $price, $this );
    }
    
    /**
     * Get discount
     *
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }
    
    /**
     * Set discount
     *
     * @param  int  $discount
     */
    public function setDiscount( $discount )
    {
        $this->discount = null;
    }
    
    /**
     * Get product id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }
    
    /**
     * Set product id
     *
     * @param  ?int  $productId
     */
    public function setProductId( $productId )
    {
        $this->productId = ( $productId ? intval( $productId ) : null );
    }
    
    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType()
    {
        return $this->priceType;
    }
    
    /**
     * Set price type
     *
     * @param  string  $priceType
     */
    public function setPriceType( $priceType )
    {
        $this->priceType = ( in_array( $priceType, array( 'percentage', 'flat' ) ) ? $priceType : 'flat' );
    }
    
    /**
     * Get regular price
     *
     * @return float
     */
    public function getRegularPrice()
    {
        return $this->regularPrice;
    }
    
    /**
     * Set regular price
     *
     * @param  string  $regularPrice
     */
    public function setRegularPrice( $regularPrice )
    {
        $this->regularPrice = ( $regularPrice ? floatval( $regularPrice ) : null );
    }
    
    /**
     * Get sale price
     *
     * @return float
     */
    public function getSalePrice()
    {
        return $this->salePrice;
    }
    
    /**
     * Set sale price
     *
     * @param  string  $salePrice
     */
    public function setSalePrice( $salePrice )
    {
        $this->salePrice = ( $salePrice ? floatval( $salePrice ) : null );
    }
    
    public function asArray()
    {
        return array(
            'pricing_type'  => $this->getPriceType(),
            'products'      => $this->getProducts(),
            'categories'    => $this->getCategories(),
            'regular_price' => $this->getRegularPrice(),
            'sale_price'    => $this->getSalePrice(),
            'discount'      => $this->getDiscount(),
            'minimum'       => $this->getMinimum(),
            'maximum'       => $this->getMaximum(),
            'group_of'      => $this->getGroupOf(),
            'product_id'    => $this->getProductId(),
        );
    }
    
    /**
     * Create instance from array
     *
     * @param  array  $data
     * @param  null|int  $productId
     *
     * @return static
     */
    public static function fromArray( $data, $productId = null )
    {
        $pricingType = ( isset( $data['pricing_type'] ) ? $data['pricing_type'] : 'flat' );
        $pricingType = ( in_array( $pricingType, array( 'flat', 'percentage' ) ) ? $pricingType : 'flat' );
        $products = ( is_array( $data['products'] ) ? $data['products'] : array() );
        $categories = ( is_array( $data['categories'] ) ? $data['categories'] : array() );
        $regularPrice = ( isset( $data['regular_price'] ) ? $data['regular_price'] : null );
        $salePrice = ( isset( $data['sale_price'] ) ? (string) $data['sale_price'] : false );
        $discount = ( isset( $data['discount'] ) ? $data['discount'] : null );
        $minimum = ( isset( $data['minimum'] ) ? $data['minimum'] : null );
        $maximum = ( isset( $data['maximum'] ) ? $data['maximum'] : null );
        $groupOf = ( isset( $data['group_of'] ) ? $data['group_of'] : null );
        $productId = ( $productId ? $productId : (( isset( $data['product_id'] ) ? (int) $data['product_id'] : null )) );
        return new static(
            $pricingType,
            $products,
            $categories,
            $regularPrice,
            $salePrice,
            $discount,
            $minimum,
            $maximum,
            $groupOf,
            $productId
        );
    }
    
    public function calculateDiscount()
    {
        if ( $this->getPriceType() === 'percentage' ) {
            return $this->getDiscount();
        }
        $product = $this->getProduct();
        
        if ( $product ) {
            $price = $this->getPrice();
            $productPrice = $product->get_price();
            if ( $productPrice > $price ) {
                return 100 - $price / $productPrice * 100;
            }
        }
        
        return 0;
    }
    
    /**
     * Validate
     *
     * @throws Exception
     */
    public function validatePricing()
    {
        if ( $this->getPriceType() === 'flat' && !$this->getPrice() ) {
            throw new Exception( esc_html__( 'The pricing fields must be filled.', 'subscribers-members-based-pricing' ) );
        }
        if ( $this->getPriceType() === 'percentage' && !$this->getDiscount() ) {
            throw new Exception( esc_html__( 'The discount is not set.', 'subscribers-members-based-pricing' ) );
        }
    }
    
    public function isValidPricing()
    {
        try {
            $this->validatePricing();
        } catch ( Exception $e ) {
            return false;
        }
        return true;
    }
    
    /**
     * Get minimum
     *
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }
    
    /**
     * Set minimum
     *
     * @param  int  $minimum
     */
    public function setMinimum( $minimum )
    {
        $this->minimum = null;
    }
    
    /**
     * Get maximum
     *
     * @return int|null
     */
    public function getMaximum()
    {
        return $this->maximum;
    }
    
    /**
     * Set maximum
     *
     * @param  int|null  $maximum
     */
    public function setMaximum( $maximum )
    {
        $this->maximum = null;
    }
    
    /**
     * Get group of
     *
     * @return int|null
     */
    public function getGroupOf()
    {
        return $this->groupOf;
    }
    
    /**
     * Set group of
     *
     * @param  int|null  $groupOf
     */
    public function setGroupOf( $groupOf )
    {
        $this->groupOf = null;
    }
    
    /**
     * Get product
     *
     * @return false|WC_Product
     */
    public function getProduct()
    {
        return wc_get_product( $this->getProductId() );
    }
    
    public function isApplicableToUser( WP_User $user )
    {
        $orders = wc_get_orders( array(
            'customer_id' => $user->ID,
            'status'      => PricingSection::getEnabledOrderStatuses(),
        ) );
        $subscriptions = array();
        if ( MembersBasedPricingPlugin::isSubscriptionsPluginEnabled() ) {
            $subscriptions = wcs_get_subscriptions( array(
                'customer_id'            => $user->ID,
                'subscriptions_per_page' => -1,
                'subscription_status'    => PricingSection::getEnabledSubscriptionStatuses(),
            ) );
        }
        foreach ( array_merge( $orders, $subscriptions ) as $order ) {
            // do not check orders generated by subscriptions
            if ( MembersBasedPricingPlugin::isSubscriptionsPluginEnabled() && wcs_order_contains_subscription( $order ) ) {
                continue;
            }
            foreach ( $order->get_items() as $item ) {
                
                if ( $item instanceof \WC_Order_Item_Product ) {
                    $variationId = $item->get_variation_id();
                    $productId = ( $variationId ? $variationId : $item->get_product_id() );
                    $categoryIds = $item->get_product()->get_category_ids();
                    // Order include products that determinate pricing
                    if ( in_array( $productId, $this->getProducts() ) ) {
                        return true;
                    }
                    // Order include product that in category that determinate pricing
                    if ( !empty(array_intersect( $categoryIds, $this->getCategories() )) ) {
                        return true;
                    }
                }
            
            }
        }
        return false;
    }

}