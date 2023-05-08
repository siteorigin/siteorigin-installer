<div class="wrap siteorigin-installer-wrap">
	<div class="siteorigin-installer-header">
		<h1 class="siteorigin-logo">
			<img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/siteorigin.svg'; ?>" />
			<?php _e( 'SiteOrigin Installer', 'siteorigin-installer' ); ?>
		</h1>

		<ul class="page-sections">
			<li class="active"><a href="#" data-section="all"><?php echo __( 'All', 'siteorigin-installer' ); ?></a></li>
			<li><a href="#" data-section="plugins"><?php echo __( 'Plugins', 'siteorigin-installer' ); ?></a></li>
			<li><a href="#" data-section="themes"><?php echo __( 'Themes', 'siteorigin-installer' ); ?></a></li>
		</ul>
	</div>

	<ul class="siteorigin-products">
		<?php
		foreach ( $products as $slug => $item ) {
			unset( $screenshot );
			unset( $update );
			if ( $slug == 'siteorigin-premium' ) {
				if (
					! apply_filters( 'siteorigin_premium_upgrade_teaser', true ) ||
					defined( 'SITEORIGIN_PREMIUM_VERSION' )
				) {
					continue;
				} else {
					$premium = true;
					$version = 1;
				}
			} else {
				if ( empty( $latest_versions[ $slug ] ) ) {
					continue;
				}
				$premium = false;
				$version = $latest_versions[ $slug ];
				$status = false;

				if ( $item['type'] == 'plugins' ) {
					$plugin_file = "$slug/$slug.php";

					if ( ! file_exists( WP_PLUGIN_DIR . "/$plugin_file" ) ) {
						$status = 'install';
					} elseif ( ! is_plugin_active( $plugin_file ) ) {
						$status = 'activate';
					}

					if ( $stuats != 'install' ) {
						$plugin = get_plugin_data( WP_PLUGIN_DIR . "/$plugin_file" );

						if (
							(
								empty( $status ) ||
								$status == 'activate'
							) &&
							version_compare( $plugin['Version'], $version, '<' )
						) {
							$update = true;
						}
					}

					if ( empty( $item['screenshot'] ) ) {
						$screenshot = 'https://plugins.svn.wordpress.org/' . $slug . '/assets/icon.svg';
					}
				} else {
					$theme = wp_get_theme( $slug );

					if ( is_object( $theme->errors() ) ) {
						$status = 'install';
					} elseif ( $theme->get_stylesheet() != $current_theme->get_stylesheet() ) {
						$status = 'activate';
					}

					if (
						(
							empty( $status ) ||
							$status == 'activate'
						) &&
						version_compare( $theme->get( 'Version' ), $version, '<' )
					) {
						$update = true;
					}
				}
			}

			if ( empty( $screenshot ) && ! empty( $item['screenshot'] ) ) {
				$screenshot = $item['screenshot'];
			}

			?>
			<li class="siteorigin-installer-item siteorigin-<?php echo esc_attr( $item['type'] ); ?> siteorigin-installer-item-<?php echo $premium || empty( $status ) ? 'active' : 'inactive'; ?>">
				<div
					class="siteorigin-installer-item-body"
					data-slug="<?php echo esc_attr( $slug ); ?>"
					data-version="<?php echo esc_attr( $version ); ?>"
				>
					<?php if ( ! empty( $screenshot ) ) { ?>
						<img class="siteorigin-installer-item-banner" src="<?php echo esc_url( $screenshot ); ?>" />
					<?php } ?>

					<h3>
						<?php echo esc_html( $item['name'] ); ?>		
					</h3>
					<p class="so-description">
						<?php echo esc_html( $item['description'] ); ?>		
					</p>

					<div class="so-type-indicator">
						<?php
						if ( $item['type'] == 'plugins' ) {
							echo __( 'Plugin', 'siteorigin-installer' );
						} else {
							echo __( 'Theme', 'siteorigin-installer' );
						}
						?>
					</div>

					<div class="so-buttons">
						<?php
						if ( $premium ) {
							$premium_url = 'https://siteorigin.com/downloads/premium/';
							$affiliate_id = apply_filters( 'siteorigin_premium_affiliate_id', '' );
							if ( $affiliate_id && is_numeric( $affiliate_id ) ) {
								$premium_url = add_query_arg( 'ref', urlencode( $affiliate_id ), $premium_url );
							}
							?>
							<a href="<?php echo esc_url( $premium_url ); ?>" target="_blank" rel="noopener noreferrer" class="button-primary">
								<?php _e( 'Get SiteOrigin Premium', 'siteorigin-installer' ); ?>		
							</a>
							<?php
						} elseif ( ! empty( $status ) || $item['type'] == 'themes' ) {
							if ( $status == 'install' ) {
								$text = __( 'Install', 'siteorigin-installer' );
							} else {
								$text = __( 'Activate', 'siteorigin-installer' );
							}
							require 'action-btn.php';
						}

						if ( ! empty( $update ) ) {
							$text = __( 'Update', 'siteorigin-installer' );
							$status = 'update';
							require 'action-btn.php';
						}
						?>

						<?php if ( $item['type'] == 'themes' ) { ?>
							<a href="<?php echo esc_url( $item['demo'] ); ?>" target="_blank" rel="noopener noreferrer" class="siteorigin-demo">
								<?php _e( 'Demo', 'siteorigin-installer' ); ?>			
							</a>
						<?php } ?>
						<a href="<?php echo esc_url( $item['documentation'] ); ?>" target="_blank" rel="noopener noreferrer" class="siteorigin-demo"><?php _e( 'Documentation', 'siteorigin-installer' ); ?></a>
					</div>

				</div>
			</li>
			<?php
		}
			?>
	</ul>

</div>
