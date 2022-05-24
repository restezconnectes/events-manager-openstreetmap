<?php
/*
Plugin Name: Events Manager OpenStreetMap
Plugin URI: https://madeby.restezconnectes.fr/project/events-manager-openstreetmap/
Description: Events Manager OpenStreetMap is a WordPress plugin for Events Manager. It allows you to replace Google Maps to OpenStreetMap on all your event locations.
Version: 2.0.4
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

if( !defined( 'EMOSM_VERSION' )) { define( 'EMOSM_VERSION', '2.0.4' ); }

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

		$emListParams = array(
            'latitude' => 47.4,
            'longitude' => 1.6,
            'zoom' => 5.5,
            'expire' => 15,
            'map_icon' => 'default',
            'map_icon_size_width' => 34,
            'map_icon_size_height' => 44,
            'tile' => 'https://{s}.tile.osm.org/{z}/{x}/{y}.png',
            'delete' => 'no',
            'css' => '/* CSS for location map */
.em-osm-thumbnail{float:left;width:30%;padding-right:2em;}
.em-osm-content{float:left;width:60%;text-align:center;}
.em-osm-readmore{padding:0.5em;text-align:center;background: #333333;color: #ffffff;}
.em-osm-readmore a:link, .em-osm-readmore a:hover, .em-osm-readmore a:visited{color: #ffffff;}
/* CSS for events map */
.em-osm-thumbnail{float:left;width:30%;padding-right:2em;}
.em-osm-event-content{float:left;width:60%;text-align:center;}
.em-osm-event-readmore{padding:0.5em;text-align:center;background: #333333;color: #ffffff;}
.em-osm-event-readmore a:link, .em-osm-event-readmore a:hover, .em-osm-event-readmore a:visited{color: #ffffff;}
/* CSS for single event map */
.em-osm-single-thumbnail{float:left;width:30%;padding-right:2em;}
.em-osm-single-content{text-align:center;}
.em-osm-single-readmore{padding:0.5em;text-align:center;background: #333333;color: #ffffff;}
.em-osm-single-readmore a:link, .em-osm-single-readmore a:hover, .em-osm-single-readmore a:visited{color: #ffffff;}
/* css to customize Leaflet default styles events  */
.customevent .leaflet-popup-tip,
.customevent .leaflet-popup-content-wrapper {background: #ffffff;color: #333333;width:400px;}
#event-map {width:100%;margin: 10px 0px 10px 0px;overflow:hidden;position:relative;margin-left: auto;margin-right: auto; }
.btn-markers {padding: 3px 5px;border: 1px solid #333;margin-top: 5px;margin-bottom: 5px;margin-right:5px;width: 70px;cursor: pointer;}
/* Legend categories*/
.catlayers {display: flex;flex-wrap: wrap;justify-content: center;padding: 10px;}
label {display: flex;align-items: center;text-transform: uppercase;cursor: pointer;}
.contentitem input {appearance: none;width: 20px;height: 20px;border: 2px solid #555555;background-clip: content-box;padding: 3px;cursor: pointer;}
.contentitem input:checked {background-color: #525252;}
ul {margin: 10px;padding: 0;list-style-type: none;}
.layer-element {list-style: none;}
/* CSS for categories events map */
.em-osm-cat-thumbnail{float:left;width:30%;padding-right:2em;}
.em-osm-cat-content{text-align:center;}
.em-osm-cat-readmore{padding:0.5em;text-align:center;background: #333333;color: #ffffff;}
.em-osm-cat-readmore a:link, .em-osm-single-readmore a:hover, .em-osm-single-readmore a:visited{color: #ffffff;}
/* Back to home button */
.back-to-home {position: absolute;top: 80px;left: 10px;width: 26px;height: 26px;z-index: 999;cursor: pointer;display: none;padding: 5px;background: #fff;border-radius: 4px;box-shadow: 0 1px 5px rgb(0 0 0 / 65%);}
.leaflet-touch .back-to-home {width: 34px;height: 34px;}
'
);

        if ( get_option('em_openstreetmap_setting', false) == false or get_option('em_openstreetmap_setting')=='' ) {
            foreach ($emListParams as $key => $option) {
                $emListParams[$key] = $option;
            }
            add_option('em_openstreetmap_setting', $emListParams);
        }


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

register_deactivation_hook( __FILE__, array( 'EM_Openstreetmap_Class', 'em_openstreetmap_desactivate' ) );
register_uninstall_hook( __FILE__, array( 'EM_Openstreetmap_Class', 'em_openstreetmap_uninstall' ) );