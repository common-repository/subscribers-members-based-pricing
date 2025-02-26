<?php defined( 'ABSPATH' ) || die;

use MeowCrew\MembersBasedPricing\Admin\ProductPage\Product;
use MeowCrew\MembersBasedPricing\Core\FileManager;
use MeowCrew\MembersBasedPricing\Entity\PricingRule;

/**
 * Available variables
 *
 * @var FileManager $fileManager
 * @var string $product_type
 * @var int $product_id
 * @var string $type
 * @var string $label
 * @var string $description
 * @var int|false $loop
 * @var PricingRule[] $pricing_rules
 */
$present_rules = array();

?>
<div class="form-field mbp-pricing-rule-block mbp-pricing-rule-block--<?php echo esc_attr( $product_type ); ?>"
	 id="mbp-pricing-rule-block-<?php echo esc_attr( $type . '-' . $product_id ); ?>"
	 data-product-type="<?php echo esc_attr( $product_type ); ?>"
	 data-rule-type="<?php echo esc_attr( $type ); ?>"
	 data-add-action="<?php echo esc_attr( Product::GET_PRODUCT_RULE_ROW_HTML__ACTION ); ?>"
	 data-add-action-nonce="<?php echo esc_attr( wp_create_nonce( Product::GET_PRODUCT_RULE_ROW_HTML__ACTION ) ); ?>"
	 data-product-id="<?php echo esc_attr( $product_id ); ?>"
	 data-loop="<?php echo esc_attr( $loop ); ?>">

	<label class="mbp-pricing-rule-block__name"><?php echo esc_attr( $label ); ?></label>
	<div class="mbp-pricing-rule-block__content">

		<div class="mbp-pricing-rules">
			<?php if ( ! empty( $pricing_rules ) ) : ?>
				<?php foreach ( $pricing_rules as $number => $pricing_rule ) : ?>
					<?php
					$present_rules[] = $number;

					$fileManager->includeTemplate( 'admin/product-page/role-specific-pricing/single-rule.php', array(
						'number'       => ++ $number,
						'pricing_rule' => $pricing_rule,
						'type'         => $type,
						'loop'         => $loop,
						'fileManager'  => $fileManager,
						'product_id'   => $product_id,
					) );

					?>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="mbp-no-rules"
					 style="<?php echo esc_attr( ! empty( $presentRoles ) ? 'display: none;' : '' ); ?>">
					<span><?php echo esc_attr( $description ); ?></span>
				</div>
			<?php endif; ?>
		</div>

		<?php
		$fileManager->includeTemplate( "admin/product-page/role-specific-pricing/identifiers/{$type}-specific.php",
			array(
				'present_rules' => $present_rules,
				'product_id'    => $product_id,
			) );
		?>
	</div>
</div>
