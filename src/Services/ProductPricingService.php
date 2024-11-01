<?php namespace MeowCrew\MembersBasedPricing\Services;

use MeowCrew\MembersBasedPricing\Core\ServiceContainerTrait;
use MeowCrew\MembersBasedPricing\PricingRulesDispatcher;
use WC_Product;

class ProductPricingService {

	use ServiceContainerTrait;

	protected $priceCache = array(
		'price'         => array(),
		'sale_price'    => array(),
		'regular_price' => array(),
	);

	public function __construct() {

		add_filter( 'woocommerce_product_get_regular_price', array(
			$this,
			'adjustRegularPrice'
		), 99, 2 );

		add_filter( 'woocommerce_product_get_sale_price', array(
			$this,
			'adjustSalePrice'
		), 99, 2 );

		add_filter( 'woocommerce_product_get_price', array(
			$this,
			'adjustPrice'
		), 99, 2 );

		// Variations
		add_filter( 'woocommerce_product_variation_get_regular_price', array(
			$this,
			'adjustRegularPrice'
		), 99, 2 );

		add_filter( 'woocommerce_product_variation_get_sale_price', array(
			$this,
			'adjustSalePrice'
		), 99, 2 );

		add_filter( 'woocommerce_product_variation_get_price', array(
			$this,
			'adjustPrice'
		), 99, 2 );

		// Variable (price range)
		add_filter( 'woocommerce_variation_prices_price', array( $this, 'adjustPrice' ), 99, 3 );

		// Variation
		add_filter( 'woocommerce_variation_prices_regular_price', array(
			$this,
			'adjustRegularPrice'
		), 99, 3 );

		add_filter( 'woocommerce_variation_prices_sale_price', array(
			$this,
			'adjustSalePrice'
		), 99, 3 );

		// Price caching
		add_filter( 'woocommerce_get_variation_prices_hash', function ( $hash, \WC_Product_Variable $product, $forDisplay ) {

			$user = wp_get_current_user();

			$hash[] = wp_json_encode( $product->get_category_ids() );

			if ( $user ) {
				$hash[] = md5( wp_json_encode( $user->roles ) );
				$hash[] = $user->ID;
			}

			return $hash;

		}, 99, 3 );

	}

	public function adjustPrice( $price, WC_Product $product ) {

		if ( array_key_exists( $product->get_id(), $this->priceCache['price'] ) ) {
			return $this->priceCache['price'][ $product->get_id() ];
		}

		$pricingRule = PricingRulesDispatcher::dispatchRule( $product->get_id() );

		if ( $pricingRule ) {
			$price = $pricingRule->getPrice();
		}

		$this->priceCache['price'][ $product->get_id() ] = $price;

		return $price;
	}

	public function adjustSalePrice( $price, WC_Product $product ) {

		if ( array_key_exists( $product->get_id(), $this->priceCache['sale_price'] ) ) {
			return $this->priceCache['sale_price'][ $product->get_id() ];
		}

		$pricingRule = PricingRulesDispatcher::dispatchRule( $product->get_id() );

		if ( $pricingRule ) {
			if ( $pricingRule->getPriceType() === 'flat' && $pricingRule->getSalePrice() ) {
				$price = $pricingRule->getSalePrice();
			} else {
				$price = $pricingRule->getPrice();
			}
		}

		$this->priceCache['sale_price'][ $product->get_id() ] = $price;

		return $price;
	}

	public function adjustRegularPrice( $price, WC_Product $product ) {

		if ( array_key_exists( $product->get_id(), $this->priceCache['regular_price'] ) ) {
			return $this->priceCache['regular_price'][ $product->get_id() ];
		}

		$pricingRule = PricingRulesDispatcher::dispatchRule( $product->get_id() );

		if ( $pricingRule ) {

			if ( $pricingRule->getPriceType() === 'flat' && $pricingRule->getRegularPrice() ) {
				$price = $pricingRule->getRegularPrice();
			} else if ( $pricingRule->getPriceType() !== 'percentage' || $this->getContainer()->getSettings()->getPercentageBasedRulesBehavior() !== 'sale_price' ) {
				// Do no modify regular price if "sale_price" chosen
				$price = $pricingRule->getPrice();
			}
		}

		$this->priceCache['regular_price'][ $product->get_id() ] = $price;

		return $price;
	}
}
