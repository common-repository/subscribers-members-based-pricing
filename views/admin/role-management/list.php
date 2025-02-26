<?php use MeowCrew\MembersBasedPricing\RoleManagement\Actions\NewRoleAction;

defined( 'ABSPATH' ) || die;

/**
 * Available variables
 *
 * @var array $roles
 * @var NewRoleAction $new_role_action
 * @var WP_List_Table $roles_table
 */
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Roles Management', 'subscribers-members-based-pricing' ); ?></h1>
	<div id="col-container">

		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h2><?php esc_html_e( 'Add new role', 'subscribers-members-based-pricing' ); ?></h2>

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

						<?php wp_nonce_field( $new_role_action->getActionSlug() ); ?>

						<div class="form-field form-required">
							<label for="role_name"><?php esc_html_e( 'Name', 'subscribers-members-based-pricing' ); ?></label>
							<input name="role_name" id="role_name" style="width: 95%" required
								   class="role_name"
								   type="text" maxlength="25">
							<p class="description"><?php esc_html_e( 'Display name', 'subscribers-members-based-pricing' ); ?></p>
						</div>

						<div class="form-field">
							<label for="inherited_role"><?php esc_html_e( 'Inherit role', 'subscribers-members-based-pricing' ); ?></label>
							<select name="inherited_role" id="inherited_role" class="role" style="width: 95%;">
								<?php foreach ( $roles as $key => $__role ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, 'customer' ); ?>><?php echo esc_html( $__role['name'] ); ?></option>
								<?php endforeach; ?>
							</select>

							<p class="description">
								<?php
								esc_html_e( 'Adopt all the capabilities from the selected role.',
									'subscribers-members-based-pricing' );
								?>
							</p>
						</div>

						<input type="hidden" name="action"
							   value="<?php echo esc_attr( $new_role_action->getActionSlug() ); ?>">

						<input type="submit" name="new_rol" id="submit" class="button button-primary"
							   value="<?php esc_html_e( 'Add Role', 'subscribers-members-based-pricing' ); ?>"
						>
					</form>

				</div>
			</div>
		</div>

		<div id="col-right" class="col-right">
			<div class="col-wrap">
				<?php $roles_table->display(); ?>
			</div>
		</div>
	</div>
</div>

