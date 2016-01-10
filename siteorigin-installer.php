<?php
/*
Plugin Name: SiteOrigin Installer
Plugin URI: https://github.com/siteorigin/siteorigin-installer/
Description: This plugin installs all the SiteOrigin themes and plugins you need to get started with your new site.
Author: SiteOrigin
Author URI: https://siteorigin.com
Version: dev
License: GNU General Public License v3.0
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

if( !defined( 'SITEORIGIN_INSTALLER_VERSION' ) ) {
	define('SITEORIGIN_INSTALLER_VERSION', 'dev');
}

require_once dirname( __FILE__ ) . '/inc/class-tgm-plugin-activation.php';
require_once dirname( __FILE__ ) . '/siteorigin-installer-theme-admin.php';

// Add WP Updates
require_once dirname( __FILE__ ) . '/inc/wp-updates-plugin.php';
new WPUpdatesPluginUpdater_1419( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));

if( !class_exists('SiteOrigin_Installer') ) {
	class SiteOrigin_Installer {

		function __construct(){
			add_action( 'tgmpa_register', array( $this, 'register_plugins' ) );
			add_action( 'siteorigin_installer_themes', array( $this, 'register_themes' ) );
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
					'name'      => 'SiteOrigin Page Builder',
					'slug'      => 'siteorigin-panels',
					'required'  => false,
				),
				array(
					'name'      => 'SiteOrigin Widgets Bundle',
					'slug'      => 'so-widgets-bundle',
					'required'  => false,
				),
				array(
					'name'      => 'SiteOrigin CSS',
					'slug'      => 'so-css',
					'required'  => false,
				)
			);

			$config = array(
				'id'           => 'tgmpa',
				'default_path' => '',
				'menu'         => 'tgmpa-install-plugins',
				'parent_slug'  => 'siteorigin-installer',
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

			$themes['siteorigin-north'] = array(
				'name' => __('SiteOrigin North', 'siteorigin-installer'),
				'demo' => 'https://demo.siteorigin.com/north/',
				'screenshot' => 'https://ts.w.org/wp-content/themes/siteorigin-north/screenshot.jpg',
				'weight' => 100,
			);

			$themes['vantage'] = array(
				'name' => __('Vantage', 'siteorigin-installer'),
				'demo' => 'https://demo.siteorigin.com/vantage/',
				'screenshot' => 'https://ts.w.org/wp-content/themes/vantage/screenshot.jpg',
				'weight' => 220,
			);

			$themes['origami'] = array(
				'name' => __('Origami', 'siteorigin-installer'),
				'demo' => 'https://demo.siteorigin.com/origami/',
				'weight' => 80,
			);

			$themes['focus'] = array(
				'name' => __('Focus', 'siteorigin-installer'),
				'demo' => 'https://demo.siteorigin.com/focus/',
				'weight' => 80,
			);

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

	}
}
SiteOrigin_Installer::single();