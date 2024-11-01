<?php defined( 'ABSPATH' ) || die;

/**
 * Available variables
 *
 * @var array $present_rules
 */
?>

<div class="mbp-add-new-rule-form">
	<select class="mbp-add-new-rule-form__identifier-selector mbp-add-new-rule-form__identifier-selector--role"
			style="width: 200px;">
		<?php foreach ( wp_roles()->roles as $key => $WPRole ) : ?>
			<?php if ( ! in_array( $key, $present_rules ) ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $WPRole['name'] ); ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
	</select>

	<button class="button mbp-add-new-rule-form__add-button"> <?php esc_attr_e( 'Setup for role', 'subscribers-members-based-pricing' ); ?></button>

	<div class="clear"></div>
</div>
