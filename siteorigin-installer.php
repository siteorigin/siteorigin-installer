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

define('SITEORIGIN_INSTALLER_VERSION', 'dev');

require_once dirname( __FILE__ ) . '/inc/class-tgm-plugin-activation.php';
require_once dirname( __FILE__ ) . '/inc/siteorigin-installer-theme.php';
require_once dirname( __FILE__ ) . '/siteorigin-installer-theme-admin.php';

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

	}

	/**
	 * Register themes that are available to install
	 */
	function register_themes( $themes ){

		$themes['bone'] = array(
			'name' => __('Bone', 'siteorigin-installer'),
			'demo' => 'https://demo.siteorigin.com/bone/',
			'weight' => 100,
		);

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
			'weight' => 80,
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

		$themes['twentytwelve'] = array(
			'name' => __('Twenty Twelve', 'siteorigin-installer'),
			'demo' => 'https://wp-themes.com/twentytwelve/',
			'weight' => 50,
		);

		return $themes;
	}

	/**
	 * Install a registered theme
	 */
	function install_theme(){
		$themes = apply_filters( 'siteorigin_installer_themes', array() );
	}

}
SiteOrigin_Installer::single();