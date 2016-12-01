<?php
/*
Plugin Name: SiteOrigin Installer
Plugin URI: https://github.com/siteorigin/siteorigin-installer/
Description: This plugin installs all the SiteOrigin themes and plugins you need to get started with your new site.
Author: SiteOrigin
Author URI: https://siteorigin.com
Version: 0.1.3
License: GNU General Public License v3.0
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

if( !defined( 'SITEORIGIN_INSTALLER_VERSION' ) ) {
	define('SITEORIGIN_INSTALLER_VERSION', '0.1.3');
}

require_once dirname( __FILE__ ) . '/inc/class-tgm-plugin-activation.php';
require_once dirname( __FILE__ ) . '/siteorigin-installer-theme-admin.php';

// Setup the Github updater
require_once dirname( __FILE__ ) . '/inc/github-plugin-updater.php';
new SiteOrigin_Installer_GitHub_Updater( __FILE__ );

if( !class_exists( 'SiteOrigin_Subversion_Theme_Updater' ) ) {
	include plugin_dir_path( __FILE__ ) . '/inc/theme-updater.php';
}

if( !class_exists('SiteOrigin_Installer') ) {
	class SiteOrigin_Installer {

		const THEME_LIST_BRANCH = 'develop';

		function __construct(){
			add_action( 'tgmpa_register', array( $this, 'register_plugins' ) );
			add_action( 'siteorigin_installer_themes', array( $this, 'register_themes' ) );
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );

			add_filter( 'siteorigin_premium_affiliate_id', array( $this, 'affiliate_id' ) );
		}

		/**
		 * Create the single instance of
		 *
		 * @return SiteOrigin_Installer
		 */
		static function single(){
			static $single;
			if( empty($single) ) {
				$single = new SiteOrigin_Installer();
			}

			return $single;
		}

		/**
		 * Register all the plugins
		 */
		function register_plugins(  ){
			$plugins = array(
				array(
					'name'      => __( 'SiteOrigin Page Builder', 'siteorigin-installer'),
					'slug'      => 'siteorigin-panels',
					'required'  => false,
				),
				array(
					'name'      => __( 'SiteOrigin Widgets Bundle', 'siteorigin-installer'),
					'slug'      => 'so-widgets-bundle',
					'required'  => false,
				),
				array(
					'name'      => __( 'SiteOrigin CSS', 'siteorigin-installer'),
					'slug'      => 'so-css',
					'required'  => false,
				)
			);

			$config = array(
				'id'           => 'tgmpa',
				'default_path' => '',
				'menu'         => 'siteorigin-plugins-installer',
				'parent_slug'  => 'siteorigin',
				'capability'   => 'activate_plugins',
				'has_notices'  => false,
				'dismissable'  => true,
				'dismiss_msg'  => '',
				'is_automatic' => true,
				'message'      => '',
				'strings' => array(
					'page_title' => __('SiteOrigin Recommended Plugins', 'siteorigin-installer'),
					'menu_title' => __('Install Plugins', 'siteorigin-installer'),
				)
			);

			tgmpa( $plugins, $config );
		}

		/**
		 * Register themes that are available to install
		 */
		function registered_themes( ){

			$themes = array();

			// Try fetch the themes from GitHub
			$response = wp_remote_get( 'https://raw.githubusercontent.com/siteorigin/siteorigin-installer/' . self::THEME_LIST_BRANCH . '/data/themes.json' );
			if( !is_wp_error( $response ) ) {
				@ $data = !empty( $response['body'] ) ? json_decode( $response['body'], true ) : false;
			}

			if( empty($themes) ) {
				$themes = json_decode( file_get_contents( plugin_dir_path(__FILE__) . '/data/themes.json' ), true );
			}

			return $themes;
		}

		/**
		 * Get the latest version of the theme
		 *
		 * @param $slug
		 *
		 * @return bool|mixed
		 */
		function get_theme_version( $slug ){
			if( !class_exists('DOMDocument') ) return false;

			// Lets make sure we're requesting the latest version
			$response = wp_remote_get( 'https://themes.svn.wordpress.org/' . urlencode( $slug ) . '/' );
			if( is_wp_error( $response ) ) return false;

			$doc = new DOMDocument();
			$doc->loadHTML( $response['body'] );
			$xpath = new DOMXPath( $doc );

			$versions = array();
			foreach( $xpath->query('//body/ul/li/a') as $el ) {
				preg_match( '/([0-9\.]+)\//', $el->getAttribute('href') , $matches);
				if( empty($matches[1]) || $matches[1] == '..' ) continue;
				$versions[] = $matches[1];
			}

			if( empty($versions) ) return false;

			usort($versions, 'version_compare');
			$latest_version = end( $versions );

			return $latest_version;
		}

		/**
		 * Get the URL we'd need to enter to switch a theme
		 *
		 * @param $slug
		 *
		 * @return string
		 */
		function get_activation_url( $slug ){
			return wp_nonce_url( self_admin_url('themes.php?action=activate&stylesheet='.$slug), 'switch-theme_'.$slug);
		}

		/**
		 * @param string $slug The theme slug
		 * @param string $version The version string
		 *
		 * @return bool|array
		 */
		function get_theme_data( $slug, $version ){
			$url = 'https://themes.svn.wordpress.org/' . urlencode($slug) . '/' . urlencode( $version ) . '/style.css';

			// Lets make sure we're requesting the latest version
			$response = wp_remote_get( $url );
			if( is_wp_error( $response ) ) return false;

			$body = substr( $response['body'], 0, 8192 );

			$fields = array(
				'name' => 'Theme Name',
				'description' => 'Description',
				'tags' => 'Tags',
				'author' => 'Author',
				'author_uri' => 'Author URI',
			);

			foreach( $fields as $field => $regex ) {
				if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $body, $match ) && $match[1] )
					$all_headers[ $field ] = strip_tags( _cleanup_header_comment( $match[1] ) );
				else
					$all_headers[ $field ] = '';
			}

			return $all_headers;
		}

		/**
		 * Display an admin notice about next steps
		 */
		function admin_notice(){
			if( ! get_option( 'siteorigin_installer_admin_notice' ) ) {
				?>
				<div class="updated">
					<p>
						<?php
						printf(
							__( "<strong>SiteOrigin Installer</strong> is ready. Start installing %s and %s to get your site going.", 'siteorigin-installer' ),
							'<a href="' . admin_url( 'admin.php?page=siteorigin-themes-installer' ) . '">' . __('themes', 'siteorigin-installer') . '</a>',
							'<a href="' . admin_url( 'admin.php?page=siteorigin-plugins-installer' ) . '">' . __('plugins', 'siteorigin-installer') . '</a>'
						);
						?>
					</p>
				</div>
				<?php
				add_option( 'siteorigin_installer_admin_notice', true, '', false );
			}
		}

		/**
		 * Get the Affiliate ID from the database.
		 *
		 * @param $id
		 *
		 * @return mixed|void
		 */
		function affiliate_id( $id ){
			if( get_option( 'siteorigin_premium_affiliate_id' ) ){
				$id = get_option( 'siteorigin_premium_affiliate_id' );
			}
			return $id;
		}

	}
}
SiteOrigin_Installer::single();