<?php namespace MeowCrew\MembersBasedPricing\Settings\CustomOptions;

use MeowCrew\MembersBasedPricing\Settings\Settings;
use WC_Admin_Settings;

class DisplayAsOption {
	
	const FIELD_TYPE = 'mbp_display_as_option';
	
	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );
		
		foreach ( array( 'enabled_subscription_statuses', 'enabled_order_statuses' ) as $option ) {
			add_action( 'woocommerce_admin_settings_sanitize_option_' . Settings::SETTINGS_PREFIX . $option, array(
				$this,
				'sanitize',
			), 3, 10 );
		}
		
	}
	
	public function render( $value ) {
		
		$option_value = (array) $value['value'];
		
		if ( ! isset( $value['id'] ) ) {
			$value['id'] = '';
		}
		
		if ( ! isset( $value['default'] ) ) {
			$value['default'] = '';
		}
		
		if ( ! isset( $value['value'] ) ) {
			$value['value'] = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
		}
		
		if ( ! isset( $value['field_name'] ) ) {
			$value['field_name'] = $value['id'];
		}
		if ( ! isset( $value['title'] ) ) {
			$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
		}
		
		if ( ! isset( $value['default'] ) ) {
			$value['default'] = '';
		}
		
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>">
					<?php echo esc_html( $value['title'] ); ?>
				</label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<select
					name="<?php echo esc_attr( $value['field_name'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
				>
					<?php
						foreach ( $value['options'] as $key => $val ) {
							?>
							
							<?php if ( $val['is_premium'] && ! meowcrew_membersbasedpricing_samspfw_fs()->is_premium() ): ?>
								<option disabled>
							<?php else: ?>
								<option value="<?php echo esc_attr( $key ); ?>"
								<?php
								if ( is_array( $option_value ) ) {
									selected( in_array( (string) $key, $option_value, true ), true );
								} else {
									selected( $option_value, (string) $key );
								}
								?>
								>
							<?php endif; ?>
							
							<?php echo esc_html( $val['label'] ); ?>
							
							<?php if ( $val['is_premium'] && ! meowcrew_membersbasedpricing_samspfw_fs()->is_premium() ): ?>
								<?php esc_html_e( ' - Available in the premium version', 'subscribers-members-based-pricing' ); ?>
							<?php endif; ?>
							</option>
							<?php
						}
					?>
				</select>
			</td>
		</tr>
		<?php
	}
	
	public function sanitize( $value ) {
		if ( null === $value ) {
			return array();
		}
		
		return array_keys( $value );
	}
}
