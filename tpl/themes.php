<div class="wrap" id="siteorigin-installer-themes">
	<h1>
		<img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/siteorigin.png' ?>" width="52" height="32" />
		<?php _e('SiteOrigin Themes', 'siteorigin-installer') ?>
	</h1>

	<div class="siteorigin-themes">
		<?php
		foreach ( $themes as $slug => $theme_data ) {
			if( empty( $latest_versions[$slug] ) ) continue;

			$theme = wp_get_theme( $slug );
			$version = $latest_versions[$slug];
			$install_url = add_query_arg( array(
				'install_theme' => $slug,
				'theme_version' => $latest_versions[$slug],
			) );
			$screenshot = !empty( $theme_data['screenshot'] ) ? $theme_data['screenshot'] : 'https://themes.svn.wordpress.org/' . $slug . '/' . $version . '/screenshot.png'

			?>
			<div class="theme-wrapper">
				<div class="theme" data-slug="<?php echo esc_attr( $slug ) ?>" data-version="<?php echo esc_url( $latest_versions[$slug] ) ?>">

					<?php
					if( ! $theme->errors() ) {
						$theme_activated = $theme->get_stylesheet() == $current_theme->get_stylesheet();
						$message = $theme_activated ? __('Activated', 'siteorigin-north') : __('Installed', 'siteorigin-north');
						?><div class="status <?php echo $theme_activated ? 'status-theme-activated' : '' ?>"><?php echo esc_html( $message ) ?></div><?php
					}
					?>

					<div class="screenshot" style="background-image: url(<?php echo esc_url($screenshot) ?>);">
						<img src="<?php echo plugin_dir_url(__FILE__) ?>../img/screenshot.png" />
					</div>
					<div class="footer">
						<h3><?php echo esc_html( $theme_data['name'] ) ?></h3>

						<div class="buttons">
							<?php if( $theme->errors() ) : ?>
								<a href="<?php echo wp_nonce_url( $install_url, 'siteorigin-install-theme' ) ?>" class="button-secondary"><?php _e( 'Install', 'siteorigin-installer' ) ?></a>
							<?php elseif( $theme->get_stylesheet() != $current_theme->get_stylesheet() ) : ?>
								<a href="<?php echo SiteOrigin_Installer_Theme_Admin::single()->get_activation_url( $slug ) ?>" class="button-secondary"><?php _e( 'Activate', 'siteorigin-installer' ) ?></a>
							<?php endif ?>
							<a href="<?php echo esc_url( $theme_data['demo'] ) ?>" target="_blank" class="button-primary siteorigin-demo"><?php _e( 'Demo', 'siteorigin-installer' ) ?></a>
						</div>
					</div>

				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>