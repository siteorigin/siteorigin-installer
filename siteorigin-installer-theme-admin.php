<?php

class SiteOrigin_Installer_Theme_Admin {

	function __construct(){
		add_action( 'admin_menu', array( $this, 'add_admin_page' ), 15, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15, 2 );
	}

	static function single(){
		static $single;
		if( empty($single) ) {
			$single = new SiteOrigin_Installer_Theme_Admin();
		}

		return $single;
	}

	/**
	 * Add the theme admin page
	 */
	function add_admin_page(){
		add_theme_page(
			__('SiteOrigin Themes', 'siteorigin-installer'),
			__('SiteOrigin Themes', 'siteorigin-installer'),
			'install_themes',
			'siteorigin-themes-installer',
			array( $this, 'display_themes_page' )
		);
	}

	/**
	 * Display the theme admin page
	 */
	function display_themes_page(){
		$themes = apply_filters( 'siteorigin_installer_themes', array() );

		$latest_versions = get_transient( 'siteorigin_installer_theme_versions' );
		if( empty($latest_versions) ) $latest_versions = array();
		$updated = false;

		foreach( $themes as $slug => $theme ) {
			if( !empty( $latest_versions[$slug] ) ) continue;

			$version = $this->get_theme_version( $slug );
			if( $version === false ) continue;

			$latest_versions[$slug] = $version;
			$updated = true;
		}

		if( $updated ) {
			// Cache for 12 hours
			set_transient( 'siteorigin_installer_theme_versions', $latest_versions, 43200 );
		}

		$current_theme = wp_get_theme();

		include plugin_dir_path( __FILE__ ) . '/tpl/themes.php';
	}

	/**
	 * @param $prefix
	 */
	function enqueue_scripts( $prefix ){
		if( $prefix !== 'appearance_page_siteorigin-themes-installer' ) return;
		wp_enqueue_script( 'siteorigin-installer-themes', plugin_dir_url(__FILE__) . '/js/themes.js', array( 'jquery' ), SITEORIGIN_INSTALLER_VERSION );
		wp_enqueue_style( 'siteorigin-installer-themes', plugin_dir_url(__FILE__) . '/css/themes.css', array( ), SITEORIGIN_INSTALLER_VERSION );
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
		$response = wp_remote_get( 'https://themes.svn.wordpress.org/' . $slug . '/' );
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
}
SiteOrigin_Installer_Theme_Admin::single();