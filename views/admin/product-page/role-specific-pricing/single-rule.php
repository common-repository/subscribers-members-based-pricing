<?php defined( 'ABSPATH' ) || die;

use MeowCrew\MembersBasedPricing\Core\FileManager;
use MeowCrew\MembersBasedPricing\Entity\PricingRule;

/**
 * Available variables
 *
 * @var PricingRule $pricing_rule
 * @var int $number
 * @var int $product_id
 * @var string $type
 * @var int|false $loop
 * @var FileManager $fileManager
 *
 */
global $wp_roles;
?>

<div class="mbp-pricing-rule">
	<div class="mbp-pricing-rule__header">
		<div class="mbp-pricing-rule__name">
			<b><?php echo esc_html( $number ? '#' . $number : __( 'Unsaved rule', 'subscribers-members-based-pricing' ) ); ?></b>
		</div>
		<div class="mbp-pricing-rule__actions">
			<span class="mbp-pricing-rule__action-toggle-view mbp-pricing-rule__action-toggle-view--open"></span>
			<a href="#" class="mbp-pricing-rule-action--delete"><?php esc_attr_e( 'Remove', 'subscribers-members-based-pricing' ); ?></a>
		</div>
	</div>
	<div class="mbp-pricing-rule__content">
		<?php
		$fileManager->includeTemplate( 'admin/product-page/role-specific-pricing/single-rule-form.php', array(
			'pricing_rule' => $pricing_rule,
			'type'         => $type,
			'loop'         => $loop,
			'product_id'   => $product_id,
		) );
		?>
	</div>
</div>
