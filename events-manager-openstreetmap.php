<?php
/*
Plugin Name: Events Manager OpenStreetMap
Plugin URI: https://madeby.restezconnectes.fr/project/events-manager-openstreetmap/
Description: Events Manager OpenStreetMap is a WordPress plugin for Events Manager. It allows you to replace Google Maps with OpenStreetMap on all your event locations.
Version: 2.0.0
Depends: Events Manager
Author: Florent Maillefaud
Author URI: https://restezconnectes.fr
Domain Path: /languages
Text Domain: events-manager-openstreetmap
*/

/*  Copyright 2022 Florent Maillefaud (email: contact at restezconnectes.fr) */


defined( 'ABSPATH' )
	or die( 'No direct load ! ' );

define( 'EMOSM_DIR', plugin_dir_path( __FILE__ ) );
define( 'EMOSM_URL', plugin_dir_url( __FILE__ ) );
define( 'EMOSM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'EMOSM_PLUGIN_URL', plugins_url().'/'.strtolower('events-manager-openstreetmap').'/');
define( 'EMOSM_TXT_DOMAIN', 'events-manager-openstreetmap');

if( !defined( 'EMOSM_VERSION' )) { define( 'EMOSM_VERSION', '2.0.0' ); }

require EMOSM_DIR . 'classes/class.php';
require EMOSM_DIR . 'includes/map.php';
require EMOSM_DIR . 'includes/metabox.php';
require EMOSM_DIR . 'includes/taxonomy.php';

class EM_OpenStreetMap {

	function __construct() {
		global $wpdb;
		//Set when to run the plugin : after EM is loaded.
		add_action( 'plugins_loaded', array(&$this,'init'), 100 );
        
	}

	function init() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		//add-ons
		if( is_plugin_active('events-manager/events-manager.php') ) {
			//add-ons
			$em_openstreetmap = new EM_Openstreetmap_Class();
            $em_openstreetmap->hooks();
    
		}else{
			add_action( 'admin_notices', array(&$this,'not_activated_error_notice') );
		}
        // Enable localization
        add_action( 'init', array(&$this,'_em_openstreetmap_load_translation' ));


	}
    
    /**
     * @return string   The theme / plugin version of the local installation.
     */
    static function get_local_version() {
        $plugin_data = get_plugin_data( __FILE__ , false );
        return $plugin_data['Version'];
    }
    static function get_plugin_file() {
        $plugin_file = __FILE__;
        return $plugin_file;
    }
    
    function _em_openstreetmap_load_translation() {
        load_plugin_textdomain( 'events-manager-openstreetmap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
    
	function not_activated_error_notice() {
		$class = "error";
		$message = __('Please ensure Events Manager is enabled for the EM OpenStreetMap to work.', EMOSM_TXT_DOMAIN);
		printf( __('<div class="%s"> <p>%s</p></div>', EMOSM_TXT_DOMAIN), $class, $message );
    }
    
}

// Start plugin
global $EM_OpenStreetMap;
$EM_OpenStreetMap = new EM_OpenStreetMap();

register_deactivation_hook( __FILE__, array( 'EM_Openstreetmap_Class', '_em_openstreetmap_desactivate' ) );
register_uninstall_hook( __FILE__, array( 'EM_Openstreetmap_Class', '_em_openstreetmap_uninstall' ) );