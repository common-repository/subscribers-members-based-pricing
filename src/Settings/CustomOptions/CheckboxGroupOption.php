<?php namespace MeowCrew\MembersBasedPricing\Settings\CustomOptions;

use MeowCrew\MembersBasedPricing\Settings\Settings;
use WC_Admin_Settings;

class CheckboxGroupOption {
	
	const FIELD_TYPE = 'mbp_checkbox_group';
	
	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );
	}
	
	public function render( $value ) {
		
		$option_value = (array) $value['value'];
		
		if ( ! isset( $value['default'] ) ) {
			$value['default'] = '';
		}
		
		if ( ! isset( $value['value'] ) ) {
			$value['value'] = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
		}
		
		if ( ! isset( $value['is_premium'] ) ) {
			$value['is_premium'] = false;
		}
		$freeStyles = '';
		
		if ( $value['is_premium'] && ! meowcrew_membersbasedpricing_samspfw_fs()->is_premium() ) {
			$freeStyles = 'opacity: .6; pointer-events: none';
		}
		
		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
			
			<td class="forminp forminp-checkbox">
				<div style="<?php echo esc_attr( $freeStyles ); ?>">
					<?php foreach ( $value['options'] as $optionKey => $optionValue ) : ?>
						<div>
							<fieldset>
								<div>
									<input <?php echo checked( in_array( $optionKey, $option_value ) ); ?>
										type="checkbox"
										id="<?php echo esc_attr( $value['id'] . '-' . $optionKey ); ?>"
										value="1"
										name="<?php echo esc_attr( $value['id'] . '[' . $optionKey . ']' ); ?>"
									>
									<label for="<?php echo esc_attr( $value['id'] . '-' . $optionKey ); ?>">
										<?php echo esc_attr( $optionValue ); ?>
									</label>
								</div>
							</fieldset>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( $value['is_premium'] && ! meowcrew_membersbasedpricing_samspfw_fs()->is_premium() ): ?>
					<p style="color: red;">
						<?php esc_html_e( 'Available in the premium version', 'subscribers-members-based-pricing' ); ?>
					</p>
					<a target="_blank" href="<?php echo esc_attr( meowcrew_membersbasedpricing_samspfw_fs_activation_url() ) ?>">
						<?php esc_html_e( 'Upgrade your plan', 'subscribers-members-based-pricing' ); ?>
					</a>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
}
