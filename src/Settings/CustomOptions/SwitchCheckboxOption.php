<?php namespace MeowCrew\MembersBasedPricing\Settings\CustomOptions;

use WC_Admin_Settings;

class SwitchCheckboxOption {

	const FIELD_TYPE = 'mbp_switch_checkbox';

	public function __construct() {
		add_action( 'woocommerce_admin_field_' . self::FIELD_TYPE, array( $this, 'render' ) );

		add_action( 'woocommerce_admin_settings_sanitize_option', function ( $value, $option, $rawValue ) {

			if ( self::FIELD_TYPE === $option['type'] ) {
				$value = in_array( $value, array( 1, 'yes' ) ) ? 'yes' : 'no';
			}

			return $value;
		}, 10, 3 );
	}

	public function render( $value ) {
		if ( ! isset( $value['id'] ) ) {
			$value['id'] = '';
		}
		if ( ! isset( $value['title'] ) ) {
			$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
		}
		if ( ! isset( $value['default'] ) ) {
			$value['default'] = '';
		}

		if ( ! isset( $value['value'] ) ) {
			$value['value'] = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
		}

		if ( ! isset( $value['on_label'] ) ) {
			$value['on_label'] = __( 'On', 'subscribers-members-based-pricing' );
		}

		if ( ! isset( $value['off_label'] ) ) {
			$value['off_label'] = __( 'Off', 'subscribers-members-based-pricing' );
		}
		if ( ! isset( $value['desc'] ) ) {
			$value['desc'] = '';
		}

		$option_value = $value['value'];

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<div>
					<input
						name="<?php echo esc_attr( $value['id'] ); ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						type="checkbox"
						value="1"
						<?php checked( $option_value, 'yes' ); ?>
						class="mbp-toggle-switch"
					/>
					<label for="<?php echo esc_attr( $value['id'] ); ?>">
						<span data-mbp-toggle-switch-on><?php echo esc_attr( $value['on_label'] ); ?></span>
						<span data-mbp-toggle-switch-off><?php echo esc_attr( $value['off_label'] ); ?></span>
					</label>
				</div>
				<p class="description"><?php echo esc_html( $value['desc'] ); ?></p>
			</td>
		</tr>
		<?php
	}
}
