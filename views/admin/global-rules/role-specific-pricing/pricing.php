<?php defined( 'ABSPATH' ) || die;
	
	use MeowCrew\MembersBasedPricing\Core\FileManager;
	use MeowCrew\MembersBasedPricing\Entity\GlobalPricingRule;
	
	/**
	 * Available variables
	 *
	 * @var  FileManager $fileManager
	 * @var  GlobalPricingRule $priceRule
	 */

?>
<div id="mbp-pricing-rule-block-global"
	 class="mbp-pricing-rule-block mbp-pricing-rule-block mbp-pricing-rule-block--global">
	<?php
		$fileManager->includeTemplate( 'admin/product-page/role-specific-pricing/single-rule-form.php', array(
			'pricing_rule' => $priceRule,
			'type'         => 'global',
			'loop'         => false,
			'identifier'   => 'global',
		) );
	?>
</div>

