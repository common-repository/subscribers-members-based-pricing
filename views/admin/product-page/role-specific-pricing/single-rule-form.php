<?php

if ( !defined( 'WPINC' ) ) {
    die;
}
use  MeowCrew\MembersBasedPricing\Entity\PricingRule ;
/**
 * Available variables
 *
 * @var string $type
 * @var int|false $loop
 * @var int $product_id
 * @var PricingRule $pricing_rule
 */
$product_id = ( isset( $product_id ) ? $product_id : null );
$loop = ( false !== $loop ? "_variation[{$loop}]" : '' );
$labelRuleAppliedFor = 'product';
switch ( $type ) {
    case 'global':
        $labelRuleAppliedFor = __( 'users in this rule', 'subscribers-members-based-pricing' );
        break;
    default:
        $labelRuleAppliedFor = '';
}
$uniqueId = preg_replace( '/[0-9]+/', '', strtolower( wp_generate_password( 20, false ) ) );
$freeVersionStyles = 'pointer-events:none; opacity: .6';
?>

<div class="mbp-pricing-rule-form mbp-pricing-rule-form--product">
	<?php 

if ( !meowcrew_membersbasedpricing_samspfw_fs()->is_premium() ) {
    ?>
		<div style="
			padding: 15px 12px;
    		background: #f5f5f5;">
			<span style="color: red;display:block; margin-bottom: 10px">
				<?php 
    esc_html_e( 'Percentage discounts and order quantity management are available in the premium version.', 'subscribers-members-based-pricing' );
    ?>
			</span>
			<a target="_blank" href="<?php 
    echo  esc_attr( meowcrew_membersbasedpricing_samspfw_fs_activation_url() ) ;
    ?>">
				<?php 
    esc_html_e( 'Upgrade your plan', 'subscribers-members-based-pricing' );
    ?>
			</a>
		</div>
	<?php 
}

?>
	
	<div class="mbp-pricing-rule-form__products" style="margin-top:30px;">
		
		<div class="mbp-pricing-rule-form__products">
			<?php 
$fieldName = "_mbp_{$type}_products{$loop}[{$uniqueId}][]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field">
				
				<label>
					<?php 
esc_html_e( 'Purchased products to determinate pricing ', 'subscribers-members-based-pricing' );
?>
				</label>
				
				<select class="wc-product-search" multiple="multiple" style="width: 80%"
						name="<?php 
echo  esc_html( $fieldName ) ;
?>"
						data-placeholder="
						<?php 
esc_attr_e( 'Search for a product &hellip;', 'subscribers-members-based-pricing' );
?>
							" data-exclude="<?php 
echo  esc_attr( $product_id ) ;
?>">
					
					<?php 
foreach ( $pricing_rule->getProducts() as $productId ) {
    ?>
						<?php 
    
    if ( $productId ) {
        ?>
							<?php 
        $product = wc_get_product( $productId );
        if ( !$product ) {
            continue;
        }
        ?>
							
							<option selected
									value="<?php 
        echo  esc_attr( $productId ) ;
        ?>"><?php 
        echo  esc_attr( $product->get_name() ) ;
        ?></option>
						<?php 
    }
    
    ?>
					
					<?php 
}
?>
				</select>
				
				<?php 
echo  wc_help_tip( esc_attr__( 'Purchased products to determinate pricing - Choose what product should be purchased by user to obtain special pricing.', 'subscribers-members-based-pricing' ) ) ;
?>
			</p>
		</div>
		
		<div class="mbp-pricing-rule-form__categories">
			<?php 
$fieldName = "_mbp_{$type}_categories{$loop}[{$uniqueId}][]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field">
				
				<label>
					<?php 
esc_html_e( 'Use any purchased products from category', 'subscribers-members-based-pricing' );
?>
				</label>
				
				<select class="wc-product-search" multiple="multiple" style="width: 80%"
						name="<?php 
echo  esc_html( $fieldName ) ;
?>"
						data-action="woocommerce_json_search_mbp_categories"
						data-placeholder="
						<?php 
esc_attr_e( 'Search for a category &hellip;', 'subscribers-members-based-pricing' );
?>" data-exclude="<?php 
echo  esc_attr( $product_id ) ;
?>">
					
					<?php 
foreach ( $pricing_rule->getCategories() as $categoryId ) {
    ?>
						<?php 
    $category = get_term_by( 'id', $categoryId, 'product_cat' );
    ?>
						
						<?php 
    
    if ( $category ) {
        ?>
							<option selected
									value="<?php 
        echo  esc_attr( $categoryId ) ;
        ?>"><?php 
        echo  esc_attr( $category->name ) ;
        ?></option>
						<?php 
    }
    
    ?>
					
					<?php 
}
?>
				</select>
				
				<?php 
echo  wc_help_tip( esc_attr__( 'Use any purchased products from category - Any purchased product from this category will help user obtain special pricing', 'subscribers-members-based-pricing' ) ) ;
?>
			</p>
		</div>
	
	</div>
	
	<hr class="mbp-title-separator">
	
	<div class="mbp-pricing-rule-form__prices">
		
		<div class="mbp-pricing-rule-form__pricing-type">
			<?php 
$fieldName = "_mbp_{$type}_pricing_type{$loop}[{$uniqueId}]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field">
				
				<label><?php 
esc_html_e( 'Pricing type', 'subscribers-members-based-pricing' );
?> </label>
				
				<input type="radio" value="flat" name="<?php 
echo  esc_html( $fieldName ) ;
?>"
					   class="mbp-pricing-type-input"
					<?php 
checked( $pricing_rule->getPriceType(), 'flat' );
?>
					   id="<?php 
echo  esc_html( $fieldName ) ;
?>-flat">
				
				<label class="mbp-pricing-rule-form__pricing-type-label"
					   for="<?php 
echo  esc_html( $fieldName ) ;
?>-flat">
					<?php 
esc_html_e( 'Flat prices', 'subscribers-members-based-pricing' );
?>
				</label>
				
				<input type="radio" value="percentage" name="<?php 
echo  esc_html( $fieldName ) ;
?>"
					   class="mbp-pricing-type-input"
					   max="99"
					<?php 
checked( $pricing_rule->getPriceType(), 'percentage' );
?>
					   id="<?php 
echo  esc_html( $fieldName ) ;
?>-percentage">
				<label class="mbp-pricing-rule-form__pricing-type-label"
					   for="<?php 
echo  esc_html( $fieldName ) ;
?>-percentage">
					<?php 
esc_html_e( 'Percentage discount', 'subscribers-members-based-pricing' );
?>
				</label>
			
			</p>
		</div>
		
		<div class="mbp-pricing-rule-form__flat_prices"
			 style="<?php 
echo  esc_attr( ( $pricing_rule->getPriceType() === 'percentage' ? 'display:none' : '' ) ) ;
?>">
			
			<section class="notice notice-warning mbp-pricing-rule-form__flat-prices-warning">
				<p>
					<?php 
esc_html_e( 'Note that prices indicated below will be applied to the whole range of products you specified in the products/categories sections. Be careful to not mess up the pricing.', 'subscribers-members-based-pricing' );
?>
				</p>
			</section>
			
			<?php 
$fieldName = "_mbp_{$type}_regular_price{$loop}[{$uniqueId}]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field">
				<label for="<?php 
echo  esc_attr( $fieldName ) ;
?>">
					<?php 
echo  esc_attr( __( 'Regular price', 'subscribers-members-based-pricing' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) ;
?>
				</label>
				<?php 
/* translators: %s: Customer role or name */
$placeholder = sprintf( __( 'Specify the regular price for %s', 'subscribers-members-based-pricing' ), $labelRuleAppliedFor );
?>
				<input type="text"
					   value="<?php 
echo  esc_attr( wc_format_localized_price( $pricing_rule->getRegularPrice() ) ) ;
?>"
					   placeholder="<?php 
echo  esc_attr( $placeholder ) ;
?>"
					   class="wc_input_price"
					   name="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   id="<?php 
echo  esc_attr( $fieldName ) ;
?>">
				
				<?php 
echo  wc_help_tip( esc_attr__( 'If you don\'t want to change standard product pricing - leave field empty.', 'subscribers-members-based-pricing' ) ) ;
?>
			</p>
			
			<?php 
$fieldName = "_mbp_{$type}_sale_price{$loop}[{$uniqueId}]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field">
				<label for="<?php 
echo  esc_attr( $fieldName ) ;
?>">
					<?php 
echo  esc_attr( __( 'Sale price', 'subscribers-members-based-pricing' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) ;
?>
				</label>
				<?php 
/* translators: %s: Customer role or name */
$placeholder = sprintf( __( 'Specify the sale price for %s', 'subscribers-members-based-pricing' ), $labelRuleAppliedFor );
?>
				<input type="text"
					   value="<?php 
echo  esc_attr( wc_format_localized_price( $pricing_rule->getSalePrice() ) ) ;
?>"
					   placeholder="<?php 
echo  esc_attr( $placeholder ) ;
?>"
					   class="wc_input_price"
					   name="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   id="<?php 
echo  esc_attr( $fieldName ) ;
?>">
				
				<?php 
echo  wc_help_tip( esc_attr__( 'If you don\'t want to change standard product pricing - leave field empty.', 'subscribers-members-based-pricing' ) ) ;
?>
			</p>
		</div>
		
		<div class="mbp-pricing-rule-form__percentage_discount"
			 style="<?php 
echo  esc_attr( ( $pricing_rule->getPriceType() === 'flat' ? 'display:none;' : '' ) ) ;
?>">
			
			<?php 
$fieldName = "_mbp_{$type}_discount{$loop}[{$uniqueId}]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field"
			   style="<?php 
echo  esc_attr( $freeVersionStyles ) ;
?>">
				<label for="<?php 
echo  esc_attr( $fieldName ) ;
?>">
					<?php 
echo  esc_attr( __( 'Discount (%)', 'subscribers-members-based-pricing' ) ) ;
?>
				</label>
				
				<input type="number" step="any" value="<?php 
echo  esc_attr( $pricing_rule->getDiscount() ) ;
?>"
					   name="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   id="<?php 
echo  esc_attr( $fieldName ) ;
?>">
			</p>
		</div>
	
	</div>
	
	<hr class="mbp-title-separator"
		data-title="<?php 
esc_attr_e( 'Product quantity control', 'subscribers-members-based-pricing' );
?>">
	
	<div class="mbp-pricing-rule-form__product_quantity">
		<div style="<?php 
echo  esc_attr( $freeVersionStyles ) ;
?>">
			<?php 
$fieldName = "_mbp_{$type}_minimum{$loop}[{$uniqueId}]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field">
				<label for="<?php 
echo  esc_attr( $fieldName ) ;
?>">
					<?php 
echo  esc_attr( __( 'Minimum quantity', 'subscribers-members-based-pricing' ) ) ;
?>
				</label>
				<?php 
// translators: %s = role name
echo  wc_help_tip( sprintf( esc_html__( 'Specify the minimal amount of products that %s can purchase.', 'subscribers-members-based-pricing' ), '<b>' . $labelRuleAppliedFor . '</b>' ) ) ;
?>
				<input type="number"
					   step="1"
					   name="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   id="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   value="<?php 
echo  esc_attr( $pricing_rule->getMinimum() ) ;
?>">
			</p>
			
			<?php 
$fieldName = "_mbp_{$type}_maximum{$loop}[{$uniqueId}]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field">
				<label for="<?php 
echo  esc_attr( $fieldName ) ;
?>">
					<?php 
echo  esc_attr( __( 'Maximum quantity', 'subscribers-members-based-pricing' ) ) ;
?>
				</label>
				<?php 
// translators: %s = role name
echo  wc_help_tip( sprintf( esc_html__( 'Specify the maximum number of products available for purchase by %s in one order.', 'subscribers-members-based-pricing' ), '<b>' . $labelRuleAppliedFor . '</b>' ) ) ;
?>
				<input type="number"
					   step="1"
					   name="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   id="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   value="<?php 
echo  esc_attr( $pricing_rule->getMaximum() ) ;
?>">
			</p>
			
			<?php 
$fieldName = "_mbp_{$type}_group_of{$loop}[{$uniqueId}]";
?>
			
			<p class="form-field <?php 
echo  esc_attr( $fieldName ) ;
?>_field">
				<label for="<?php 
echo  esc_attr( $fieldName ) ;
?>">
					<?php 
echo  esc_attr( __( 'Quantity step', 'subscribers-members-based-pricing' ) ) ;
?>
				</label>
				<?php 
// translators: %s = role name
echo  wc_help_tip( sprintf( esc_html__( 'Specify by how many products quantity will increase or decrease when a customer adds the product to the cart for purchase by %s. Leave blank if products can be added one by one.', 'subscribers-members-based-pricing' ), '<b>' . $labelRuleAppliedFor . '</b>' ) ) ;
?>
				<input type="number"
					   step="1"
					   name="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   id="<?php 
echo  esc_attr( $fieldName ) ;
?>"
					   value="<?php 
echo  esc_attr( $pricing_rule->getGroupOf() ) ;
?>">
			</p>
		</div>
	
	</div>

</div>
