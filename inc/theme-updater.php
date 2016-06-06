<?php

if( !class_exists( 'SiteOrigin_Subversion_Theme_Updater' ) ) {

	/**
	 * Class SiteOrigin_Premium_Theme_Updater
	 *
	 * This class just makes sure we're updating to the latest version of SiteOrigin themes available on WordPress.org.
	 *
	 * New themes and updates can take several months to be updated.
	 */
	class SiteOrigin_Subversion_Theme_Updater {

		function __construct(){
			add_filter( 'pre_set_site_transient_update_themes', array( &$this, 'check_for_update' ), 12 );

			// For development purposes
			// set_site_transient('update_themes', null);
		}

		static function single(){
			static $single;

			if( empty($single) ) {
				$single = new self();
			}

			return $single;
		}

		function check_for_update( $transient ){
			if ( empty($transient->checked) ) return $transient;
			$theme = wp_get_theme();

			// Ignore this for Premium SiteOrigin Themes
			if ( defined('SITEORIGIN_IS_PREMIUM') ) return $transient;
			if ( strpos( $theme->get('AuthorURI'), 'siteorigin.com' ) === false ) return $transient;
			if ( !class_exists('DOMDocument') ) return $transient;

			// Lets make sure we're requesting the latest version
			$template = $theme->get_template();
			$response = wp_remote_get( 'https://themes.svn.wordpress.org/' . $template . '/' );
			if( is_wp_error( $response ) ) return $transient;

			$doc = new DOMDocument();
			$doc->loadHTML( $response['body'] );
			$xpath = new DOMXPath( $doc );

			$versions = array();
			foreach( $xpath->query('//body/ul/li/a') as $el ) {
				preg_match( '/([0-9\.]+)\//', $el->getAttribute('href') , $matches);
				if( empty($matches[1]) || $matches[1] == '..' ) continue;
				$versions[] = $matches[1];
			}

			if( empty($versions) ) return $transient;

			usort($versions, 'version_compare');
			$latest_version = end( $versions );
			$theme_version = $theme->get('Version');

			// Store this in the transient
			if( !empty($transient->response) ) {
				$transient->response[$template] = array(
					'new_version' => $latest_version,
					'url' => add_query_arg( 'action', 'changelog', $theme->get('ThemeURI') ),
					'package' => 'https://wordpress.org/themes/download/' . $template . '.' . $latest_version . '.zip'
				);
			}

			return $transient;
		}

	}

}

SiteOrigin_Subversion_Theme_Updater::single();