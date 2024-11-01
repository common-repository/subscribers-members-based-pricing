<?php use MeowCrew\MembersBasedPricing\Core\FileManager;
use MeowCrew\MembersBasedPricing\Admin\ProductPage\PricingRulesManager;

defined( 'ABSPATH' ) || die;

/**
 * Available variables
 *
 * @var $fileManager FileManager
 * @var $post WP_Post
 * @var $product_type string
 * @var $loop int
 */

?>
	<div class="clear"></div>
<?php

$fileManager->includeTemplate( 'admin/product-page/role-specific-pricing/pricing-block.php', array(
	'fileManager'   => $fileManager,
	'product_id'    => $post->ID,
	'loop'          => $loop,
	'product_type'  => $product_type,
	'type'          => 'product',
	'label'         => __( 'Pricing for subscribers/members', 'subscribers-members-based-pricing' ),
	'description'   => '',
	'pricing_rules' => PricingRulesManager::getProductPricingRules( $post->ID, false ),
) );

