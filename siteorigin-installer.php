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
				'has_notices'  => true,
				'dismissable'  => true,
				'dismiss_msg'  => '',
				'is_automatic' => true,
				'message'      => '',
				'strings' => array(
					'page_title' => __('SiteOrigin Recommended Plugins', 'siteorigin-installer'),
					'menu_title' => __('SiteOrigin Plugins', 'siteorigin-installer'),
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

	}
}
SiteOrigin_Installer::single();