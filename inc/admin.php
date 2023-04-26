<?php

if ( ! class_exists( 'SiteOrigin_Installer_Admin' ) ) {
	class SiteOrigin_Installer_Admin {
		public function __construct() {
			add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
			add_action( 'wp_ajax_so_installer_dismiss', array( $this, 'dismiss_notice' ) );
			add_action( 'wp_ajax_siteorigin_installer_manage', array( $this, 'manage_product' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );
		}

		public static function single() {
			static $single;

			return empty( $single ) ? $single = new self() : $single;
		}

		public function display_admin_notices() {
			global $pagenow;
			
			if (
				! get_option( 'siteorigin_installer_admin_dismissed' ) &&
				(
					$pagenow != 'admin.php' ||
					$_GET['page'] != 'siteorigin-installer'
				) 
			) {
				$dismiss_url = wp_nonce_url( add_query_arg( array( 'action' => 'so_installer_dismiss' ), admin_url( 'admin-ajax.php' ) ), 'so_installer_dismiss' );
				?>
				<div id="siteorigin-installer-notice" class="notice notice-warning">
					<p>
						<?php
						printf(
							__( "%s installing recommended SiteOrigin plugins and a SiteOrigin theme to get your site going.", 'siteorigin-installer' ),
							'<a href="' . esc_url( admin_url( 'admin.php?page=siteorigin-installer' ) ) . '" target="_blank" rel="noopener noreferrer" >' . __( 'Click here', 'siteorigin-installer' ) . '</a>',
						);
						?>
					</p>
					<a href="<?php echo $dismiss_url; ?>" class="siteorigin-notice-dismiss"></a>
				</div>
				<?php
				wp_enqueue_script(
					'siteorigin-installer-notice',
					SITEORIGIN_INSTALLER_URL . 'js/notices.js',
					array( 'jquery' ),
					SITEORIGIN_INSTALLER_VERSION
				);

				wp_enqueue_style(
					'siteorigin-installer-notice',
					SITEORIGIN_INSTALLER_URL . 'css/notices.css'
				);
			}
		}

		public function dismiss_notice() {
			check_ajax_referer( 'so_installer_dismiss' );
			update_option( 'siteorigin_installer_admin_dismissed', true );

			die();
		}

		public function admin_menu() {
			global $menu;

			if ( empty( $GLOBALS['admin_page_hooks']['siteorigin'] ) ) {
				add_menu_page(
					__( 'SiteOrigin', 'siteorigin-installer' ),
					__( 'SiteOrigin', 'siteorigin-installer' ),
					false,
					'siteorigin',
					false,
					SITEORIGIN_INSTALLER_URL . '/img/icon.svg',
					66
				);
			}

			add_submenu_page(
				'siteorigin',
				__( 'Installer', 'siteorigin-installer' ),
				__( 'Installer', 'siteorigin-installer' ),
				'manage_options',
				'siteorigin-installer',
				array( $this, 'display_admin_page' )
			);
		}

		public function enqueue_scripts( $prefix ) {
			if ( $prefix !== 'siteorigin_page_siteorigin-installer' ) {
				return;
			}

			wp_enqueue_style(
				'siteorigin-installer',
				SITEORIGIN_INSTALLER_URL . '/css/admin.css',
				array(),
				SITEORIGIN_INSTALLER_VERSION
			);

			wp_enqueue_script(
				'siteorigin-installer',
				SITEORIGIN_INSTALLER_URL . '/js/script.js',
				array( 'jquery' ),
				SITEORIGIN_INSTALLER_VERSION
			);

			wp_localize_script(
				'siteorigin-installer',
				'soInstallerAdmin',
				array(
					'manageUrl' => wp_nonce_url( admin_url( 'admin-ajax.php?action=siteorigin_installer_manage' ), 'siteorigin-installer-manage' ),
					'activateText' => __( 'Activate', 'siteorigin-installer' ),
				)
			);
		}

		public function manage_product() {
			check_ajax_referer( 'siteorigin-installer-manage' );

			if ( empty( $_POST['slug'] ) || empty( $_POST['task'] ) || empty( $_POST['type'] ) || empty( $_POST['version'] ) ) {
				die();
			}

			$product_url = 'https://wordpress.org/' . urlencode( $_POST['type'] ) . '/download/' . urlencode( $_POST['slug'] ) . '.' . urlencode( $_POST['version'] ) . '.zip';
			// check_ajax_referer( 'so_installer_manage' );
			if ( ! class_exists( 'WP_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}
			$upgrader = new WP_Upgrader();

			if ( $_POST['type'] == 'plugins' ) {
				if ( $_POST['task'] == 'install' || $_POST['task'] == 'update' ) {
					$upgrader->run( array(
						'package' => $product_url,
						'destination' => WP_PLUGIN_DIR,
						'clear_destination' => true,
						'abort_if_destination_exists' => false,
						'hook_extra' => array(
							'type' => 'plugin',
							'action' => 'install',
						),
					) );
				} elseif ( $_POST['task'] == 'activate' ) {
					@activate_plugin( $_POST['slug'] . '/' . $_POST['slug'] . '.php' );
				}
			} elseif ( $_POST['type'] == 'themes' ) {
				if ( $_POST['task'] == 'install' || $_POST['task'] == 'update' ) {
					$upgrader->run( array(
						'package' => $product_url,
						'destination' => get_theme_root(),
						'clear_destination' => true,
						'clear_working' => true,
						'abort_if_destination_exists' => false,
					) );
				} elseif ( $_POST['task'] == 'activate' ) {
					switch_theme( $_POST['slug'] );
				}
			}
			die();
		}

		/**
		 * Display the theme admin page
		 */
		public function display_admin_page() {
			$products = apply_filters( 'siteorigin_installer_products', json_decode( file_get_contents( SITEORIGIN_INSTALLER_DIR. '/data/products.json' ), true ) );

			$latest_versions = get_transient( 'siteorigin_installer_versions' );

			if ( empty( $latest_versions ) ) {
				$latest_versions = array();

				foreach ( $products as $slug => $item ) {
					if ( $slug == 'siteorigin-premium' ) {
						continue;
					}
					$version = SiteOrigin_Installer::single()->get_version( $slug, $item['type'] );

					if ( $version === false ) {
						continue;
					}
					$latest_versions[ $slug ] = $version;
				}

				set_transient( 'siteorigin_installer_versions', $latest_versions, 43200 );
			}

			$current_theme = wp_get_theme();

			foreach ( $products as $slug => $item ) {
				if ( $item['type'] == 'theme' && $slug == $current_theme->get_stylesheet() ) {
					$themes[ $slug ]['weight'] = 999;
				}
			}

			uasort( $products, array( $this, 'sort_compare' ) );

			include SITEORIGIN_INSTALLER_DIR . '/tpl/admin.php';
		}

		/**
		 * Comparison function for sorting
		 *
		 * @return int
		 */
		public function sort_compare( $a, $b ) {
			if ( empty( $a['weight'] ) || empty( $b['weight'] ) ) {
				return 0;
			}

			return $a['weight'] < $b['weight'] ? 1 : -1;
		}
	}
}
SiteOrigin_Installer_Admin::single();
