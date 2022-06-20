<?php

class EM_Openstreetmap_Class {
    
	public function hooks() {
     
        $this->text_domain = 'events-manager-openstreetmap';

        /* Version du plugin */
        $option['em_openstreetmap_version'] = EMOSM_VERSION;
        if( !get_option('em_openstreetmap_version') ) {
            add_option('em_openstreetmap_version', $option);
        } else if ( get_option('em_openstreetmap_version') != EMOSM_VERSION ) {
            update_option('em_openstreetmap_version', EMOSM_VERSION);
        }
        $this->plugin_file = EM_openstreetmap::get_plugin_file();

        //register_deactivation_hook(__FILE__, 'em_openstreetmap_uninstall');
        add_action( 'admin_menu', array( $this, 'em_openstreetmap_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'em_openstreetmap_admin_head') );
        add_action( 'wp_enqueue_scripts', array( $this, 'em_openstreetmap_assets'), 1 );
        add_filter( 'plugin_action_links', array( $this, 'em_openstreetmap_plugin_actions'), 10, 2 );
        add_action( 'wp_head', array( $this, 'em_markers_js') );

        add_filter('manage_edit-location_columns', array( $this, 'add_manage_edit_icon_columns'));
        add_action('manage_location_posts_custom_column', array( $this, 'add_manage_icon_custom_column'), 10, 2);

        add_filter('manage_edit-event_columns', array( $this, 'add_manage_edit_icon_columns'));
        add_action('manage_event_posts_custom_column', array( $this, 'add_manage_icon_custom_column'), 10, 2);

        add_filter( "manage_edit-event-categories_columns", array( $this, 'add_manage_edit_icon_columns'), 10);
        add_action( "manage_event-categories_custom_column", array( $this, 'add_manage_cat_column_icon'), 10, 3);
    }

    // parm order: value_to_display, $column_name, $tag->term_id
    // filter: manage_{$taxonomy}_custom_column
    function add_manage_cat_column_icon( $value, $column_name, $tax_id ){

        switch( $column_name ) {
            case 'em_osm_icon':
                // your code here
                $icon_id = get_term_meta ( $tax_id, 'em-categories-icon-id', true );
                if ( $icon_id ) {
                    $value = wp_get_attachment_image ( $icon_id, 'thumbnail' );
                } else {
                    $value = '';
                }
            break;

            // ... similarly for more columns
            default:
            break;
        } 

        return $value; // this is the display value
    }

    /* Ajout de champ icon */
    function add_manage_edit_icon_columns($columns){
        $columns['em_osm_icon'] = __( 'Icon', EMOSM_TXT_DOMAIN );
        return $columns;
    }
    
    function add_manage_icon_custom_column($column_name, $post_id){
        
        global $EM_Event;

        if ( $column_name == 'em_osm_icon' ) {

            if( $_GET['post_type']== 'location' ) {

                // Je vais chercher l'icon choisit
                $mapIcon = get_post_meta($post_id, '_location_icon', true);
                if( isset($mapIcon) && $mapIcon != '') { $icon = esc_html($mapIcon); } else { $icon = 'default'; }

                // Vérifie si il y a un icon custom
                $customIcon = get_post_meta($post_id, '_location_custom_icon', true);
                
                // Si il y un icon custom, on vérifie si il a seclectionné Custom Icon
                $urlIcon = esc_url(EMOSM_URL.'images/markers/'.$icon.'.png');

                // On va chercher l'icon de la categorie d'evenement
                if( isset($mapIcon) && $mapIcon=="none" ) { 
                    $EM_Categories = $EM_Event->get_categories();
                    if( $EM_Categories ) {
                        foreach( $EM_Categories AS $EM_Category){
                            $iconId = get_term_meta($EM_Category->term_id, 'em-categories-icon-id', true);
                            if( $iconId !='' && is_numeric($iconId) ) {
                                $urlIcon = wp_get_attachment_url($iconId);
                            }
                        }
                    }
                }

            } else if ($_GET['post_type']== 'event') {

                // Je vais chercher l'icon choisit
                $mapIcon = get_post_meta($post_id, '_location_osm_map_icon', true);
                if( isset($mapIcon) && $mapIcon != '') { $icon = esc_html($mapIcon); } else { $icon = 'default'; }
                $urlIcon = esc_url(EMOSM_URL.'images/markers/'.$icon.'.png');

                // On va chercher l'icon de la categorie d'evenement
                if( isset($mapIcon) && $mapIcon=="none" ) { 
                    $EM_Categories = $EM_Event->get_categories();
                    if( $EM_Categories ) {
                        foreach( $EM_Categories AS $EM_Category){
                            $iconId = get_term_meta($EM_Category->term_id, 'em-categories-icon-id', true);
                            if( $iconId !='' && is_numeric($iconId) ) {
                                $urlIcon = wp_get_attachment_url($iconId);
                            }
                        }
                    }
                }
            }

            echo '<img src="'.$urlIcon.'" width="18">';
        
        }

        

    }
    /* FIN - Ajout de champ projet sur la liste des defis */

    function em_markers_js() {

        global $post;

        $upload_dir = wp_upload_dir();

        // Récupère les paramètres sauvegardés
        if(get_option('em_openstreetmap_setting')) { extract(get_option('em_openstreetmap_setting')); }
        $paramMMode = get_option('em_openstreetmap_setting');

        $em_page_location_id = get_option('em_openstreetmap_location_page');
        $em_page_events_id = get_option('em_openstreetmap_events_page');

        if( isset($post->ID) && !empty($post->ID) && $post->ID == $em_page_location_id ) { $type = 'location'; } 
        if( isset($post->ID) && !empty($post->ID) && $post->ID == $em_page_events_id) { $type ='events'; }

        if( isset($type) ) {
            echo '<script src="'.esc_js(str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $this->em_openstreetmap_generate(esc_html($type)))).'?ver='.EMOSM_VERSION.''.rand().'"></script>';
        }
        if( isset($paramCss) && $paramCss != '' ) {
            echo "<style id='emosm-css' type='text/css'>".esc_html($paramMMode['css'])."</style>";
        }
    }

    function em_openstreetmap_admin_head() {

        global $post;
        $post_type = get_post_type( $post );
        // If you're not including an image upload then you can leave this function call out
        if( $post_type == 'location') {

            wp_enqueue_style( 'emosm-leafletcss', EMOSM_URL.'styles/leaflet.min.css' );
            wp_enqueue_script( 'emosm-leafletjs', EMOSM_URL.'scripts/leaflet.min.js', 'jquery', EMOSM_VERSION);
            wp_enqueue_style( 'emosm-markercluster', EMOSM_URL.'styles/MarkerCluster.css' );
            wp_enqueue_style( 'emosm-markercluster-default', EMOSM_URL.'styles/MarkerCluster.Default.css' );
            wp_enqueue_script( 'emosm-markerclusterjs', EMOSM_URL.'scripts/leaflet.markercluster.js', 'jquery', EMOSM_VERSION);
            wp_enqueue_script( 'emosm-esrileafletjs', EMOSM_URL.'scripts/esri-leaflet.min.js', 'jquery', EMOSM_VERSION);
            wp_enqueue_script( 'emosm-leafletgeocoderjs', EMOSM_URL.'scripts/esri-leaflet-geocoder.js', 'jquery', EMOSM_VERSION);
            wp_enqueue_style( 'emosm-leafletgeocodertcss', EMOSM_URL.'styles/esri-leaflet-geocoder.css' );
            wp_enqueue_script( 'emosm-leafletprovidersjs', EMOSM_URL.'scripts/leaflet-providers.js', 'jquery', EMOSM_VERSION);

            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');

            wp_register_script('em-upload', EMOSM_URL.'scripts/em-script.js', array('jquery','media-upload','thickbox'));
            wp_enqueue_script('em-upload');

            // If you're not including an image upload then you can leave this function call out
            wp_enqueue_media();

            // Now we can localize the script with our data.
            wp_localize_script( 'em-upload', 'Data', array(
              'textebutton'  =>  __( 'Choose This Image', EMOSM_TXT_DOMAIN ),
              'title'  => __( 'Choose Image', EMOSM_TXT_DOMAIN ),
            ) );

        }
        
    }

    function em_openstreetmap_assets() {

        global $post;
        $post_type = get_post_type( $post );

        $em_page_location_id = get_option('em_openstreetmap_location_page');
        $em_page_events_id = get_option('em_openstreetmap_events_page');
        $em_page_cat_id = get_option('em_openstreetmap_categories_page');

        $prinScript = 0;

        // If you're not including an image upload then you can leave this function call out
        if( (!empty($post_type) && $post_type == 'location' || $post_type == 'event') ) { $prinScript = 1; }
        
        if( isset($post->ID) && !empty($post->ID) ) {

            if( isset($em_page_location_id) && $em_page_location_id>=1 && $post->ID == $em_page_location_id ) { $prinScript = 1; }
            if( isset($em_page_events_id) && $em_page_events_id>=1 && $post->ID == $em_page_events_id ) { $prinScript = 1; }
            if( isset($em_page_cat_id) && $em_page_cat_id>=1 && $post->ID == $em_page_cat_id ) { $prinScript = 1; }
            
            if( isset($prinScript) && $prinScript == 1 ) {

                // Déclarer un autre fichier CSS
                wp_enqueue_style('emosm-leaflet_css', EMOSM_URL.'styles/leaflet.min.css', array(), EMOSM_VERSION);
                // Déclarer le JS
                wp_enqueue_script('emosm_leaflet_js', EMOSM_URL.'scripts/leaflet.min.js', array( 'jquery' ), EMOSM_VERSION, false);
                wp_enqueue_style('emosm-markercluster', EMOSM_URL.'styles/MarkerCluster.css' );
                wp_enqueue_style('emosm-markercluster-default', EMOSM_URL.'styles/MarkerCluster.Default.css' );
                wp_enqueue_script('emosm-markerclusterjs', EMOSM_URL.'scripts/leaflet.markercluster.min.js', 'jquery', EMOSM_VERSION);
                wp_enqueue_script('emosm-esrileafletjs', EMOSM_URL.'scripts/esri-leaflet.min.js', 'jquery', EMOSM_VERSION);
                wp_enqueue_script('emosm-providers', EMOSM_URL.'scripts/leaflet-providers.js', array( 'jquery' ), EMOSM_VERSION, false);
                wp_enqueue_style('emosm-stylemap', EMOSM_URL.'styles/style.min.css');
                wp_enqueue_script('emosm-minimap', EMOSM_URL.'scripts/Control.MiniMap.min.js', array( 'jquery' ), EMOSM_VERSION, false);
                wp_enqueue_style('emosm-minimap-css', EMOSM_URL.'styles/Control.MiniMap.css');
                wp_enqueue_script('emosm-leafletgeocoderjs', EMOSM_URL.'scripts/esri-leaflet-geocoder.js', array( 'jquery' ), EMOSM_VERSION);
                wp_enqueue_style('emosm-leafletgeocodertcss', EMOSM_URL.'styles/esri-leaflet-geocoder.min.css');

            }
        }

    }

    public static function em_openstreetmap_desactivate() {

        // Récupère les paramètres sauvegardés
        if(get_option('em_openstreetmap_setting')) { extract(get_option('em_openstreetmap_setting')); }
        $paramMMode = get_option('em_openstreetmap_setting');

        if( isset($paramMMode['delete']) && $paramMMode['delete'] == 'yes' ) {

            delete_option('em_openstreetmap_version');
            delete_option('em_openstreetmap_setting');
    
            wp_delete_post(get_option('em_openstreetmap_location_page', true), true);
            wp_delete_post(get_option('em_openstreetmap_events_page', true), true);
            wp_delete_post(get_option('em_openstreetmap_categories_page', true), false);
    
            delete_option('em_openstreetmap_location_page');
            delete_option('em_openstreetmap_events_page');
            delete_option('em_openstreetmap_categories_page');
        }

    }

    public static function em_openstreetmap_uninstall() {

        delete_option('em_openstreetmap_version');
        delete_option('em_openstreetmap_setting');

        wp_delete_post( get_option('em_openstreetmap_location_page', true), true);
        wp_delete_post( get_option('em_openstreetmap_events_page', true), true);
        wp_delete_post( get_option('em_openstreetmap_categories_page', true), false);

        delete_option('em_openstreetmap_location_page');
        delete_option('em_openstreetmap_events_page');
        delete_option('em_openstreetmap_categories_page');

    }

    function em_openstreetmap_admin_menu() {

        if ( empty ( $GLOBALS['admin_page_hooks']['made-by-restezconnectes'] ) ) {
            add_menu_page('Made By RC Settings', 'Made By RC', 'manage_options', 'made-by-restezconnectes', array( $this, 'em_openstreetmap_home_page'), EMOSM_URL.'assets/madeby-rc.png');
        }

        add_submenu_page( 'made-by-restezconnectes', __('EM OSM', EMOSM_TXT_DOMAIN), __('EM OSM', EMOSM_TXT_DOMAIN), 'manage_options', 'em_openstreetmap_settings_page', array( $this, 'em_openstreetmap_settings_page') );
        // Add submenu pour Event Manager
        add_submenu_page( 'edit.php?post_type=event', __('OpenStreetMap', EMOSM_TXT_DOMAIN), __('OpenStreetMap', EMOSM_TXT_DOMAIN), 'manage_options', 'em_openstreetmap_settings_page', array( $this, 'em_openstreetmap_settings_page') );

        $em_page_location_id = get_option('em_openstreetmap_location_page', false); // Les id des pages sont en mémoire
		if( isset($em_page_location_id) &&  $em_page_location_id == false ) { // Test si elles existent pour ne pas les recréer
			
			$post_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_content' => '[em_osmap]',
				'post_excerpt' => '',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_title' => __('Locations Map', EMOSM_TXT_DOMAIN),
				'menu_order' => 0
			);
			$em_page_location_id = wp_insert_post($post_data, false);
			add_option('em_openstreetmap_location_page', $em_page_location_id);
		}

        $em_pages_events_id = get_option('em_openstreetmap_events_page', false); // Les id des pages sont en mémoire
		if( isset($em_pages_events_id) && $em_pages_events_id == false ) { // Test si elles existent pour ne pas les recréer
			
			$post_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_content' => '[em_osmap type="events"]',
				'post_excerpt' => '',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_title' => __('Events Map', EMOSM_TXT_DOMAIN),
				'menu_order' => 0
			);
			$em_pages_events_id = wp_insert_post($post_data, false);
			add_option('em_openstreetmap_events_page', $em_pages_events_id);
		}
        $em_pages_cat_id = get_option('em_openstreetmap_categories_page', false); // Les id des pages sont en mémoire
		if( isset($em_pages_cat_id) && $em_pages_cat_id == false ) { // Test si elles existent pour ne pas les recréer
			
			$post_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_content' => '[em_osmap_categories]',
				'post_excerpt' => '',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_title' => __('Categories Map', EMOSM_TXT_DOMAIN),
				'menu_order' => 0
			);
			$em_pages_cat_id = wp_insert_post($post_data, false);
			add_option('em_openstreetmap_categories_page', $em_pages_cat_id);
		}

        if ( isset($_GET['page']) && $_GET['page']=='em_openstreetmap_settings_page' ) {
            $emsom_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
            wp_localize_script('jquery', 'cm_settings', $emsom_settings);
            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');
        }
    }

     // Add "Réglages" link on plugins page
     function em_openstreetmap_plugin_actions( $links, $file ) {
            
        //return array_merge( $links, $settings_link );
        if ($file != EMOSM_PLUGIN_BASENAME) {
            return $links;
        } else {
            $settings_link = '<a href="admin.php?page=em_openstreetmap_settings_page">'.__( 'Settings', EMOSM_TXT_DOMAIN ).'</a>';
            array_unshift( $links, $settings_link );
            return $links;
        }
    }
    
    function em_openstreetmap_home_page() {

        //must check that the user has the required capability
        if (!current_user_can('manage_options')) {
            wp_die( __("You do not have sufficient privileges to access this page.", EMOSM_TXT_DOMAIN) );
        }
		include(EMOSM_DIR.'admin/home.php');
    }

    function em_openstreetmap_settings_page() {
        //must check that the user has the required capability
        if (!current_user_can('manage_options')) {
            wp_die( __("You do not have sufficient privileges to access this page.", EMOSM_TXT_DOMAIN) );
        }
        include(EMOSM_DIR.'admin/settings.php');
    }
    
    function em_openstreetmap_cat() {
        global $wpdb;

    }
    
    public static function em_openstreetmap_folder_uploads($folder = '') {

        global $post;
    
        $upload_dir = wp_upload_dir();
    
        $tmpDirectory = $upload_dir['basedir'].'/em-map-export/tmp';
        if( is_dir($tmpDirectory) == false ) {
            $files = array(
                array(
                    'base' 		=> $upload_dir['basedir'] . '/em-map-export/',
                    'file' 		=> 'index.php',
                    'content' 	=> '<?php // Silence is Golden'
                ),
                array(
                    'base' 		=> $upload_dir['basedir'] . '/em-map-export/tmp',
                    'file' 		=> 'index.php',
                    'content' 	=> '<?php // Silence is Golden'
                )
            );
    
            foreach ( $files as $file ) {
                if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
                    if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
                        fwrite( $file_handle, $file['content'] );
                        fclose( $file_handle );
                    }
                }
            }
    
            add_option('map_path_temp', $upload_dir['basedir'] . '/em-map-export/tmp');
        } else if( empty(get_option('map_path_temp')) ) {
            add_option('map_path_temp', $upload_dir['basedir'] . '/em-map-export/tmp');
        }
    
        $createDirectory = $upload_dir['basedir'].'/em-map-export';
        if( is_dir($createDirectory) == false ) {
            //mkdir($newDirectory, 0755);
            $files = array(
                array(
                    'base' 		=> $upload_dir['basedir'] . '/em-map-export/',
                    'file' 		=> '.htaccess',
                    'content' 	=> 'Options -Indexes'
                )
            );
    
            foreach ( $files as $file ) {
                if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
                    if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
                        fwrite( $file_handle, $file['content'] );
                        fclose( $file_handle );
                    }
                }
            }
    
        }
    
        if( isset($folder) && !empty($folder) ) {
    
            $subDirectory = $upload_dir['basedir'].'/em-map-export/'.$folder;
            if ( is_dir($subDirectory) == false ) {
                $files = array(
                    array(
                        'base' 		=> $subDirectory,
                        'file' 		=> 'index.php',
                        'content' 	=> '<?php // Silence is Golden'
                    )
                );
    
                foreach ( $files as $file ) {
                    if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
                        if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
                            fwrite( $file_handle, $file['content'] );
                            fclose( $file_handle );
                        }
                    }
                }
            }
            $createDirectory = $upload_dir['basedir'].'/em-map-export/'.$folder;
    
        }
    
        return $createDirectory;
    
    }

    public static function em_openstreetmap_generate($type = 'location', $cat = '', $limit = 0, $forceGenerate = 0) {

        global $EM_Location;
        global $EM_Event;

        $expire = get_option('em_openstreetmap_expire' );
        if( empty($expire) ) { $expire == 15; }
        // Temps définie par default = 15 jours
        $quinzeJours = mktime(0, 0, 0, date("m"), (date("d")+$expire), date("Y"));

        // Si l'option n'existe pas on la crée
        $generateDateMap = get_option('em_generate_map');
        if ( empty($generateDateMap) ) {
            add_option('em_generate_map', $quinzeJours);
        }
        
        // On compare la date d'aujourd'hui avec celle en mémoire dans les options
        $datenow = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        if( $generateDateMap == $datenow ) {
            update_option('em_generate_map', $quinzeJours);
            $forceGenerate = 1;
        }

        // Récupère les paramètres sauvegardés
        if(get_option('em_openstreetmap_setting')) { extract(get_option('em_openstreetmap_setting')); }
        $paramMMode = get_option('em_openstreetmap_setting');

        if(isset($paramMMode['latitude']) && $paramMMode['latitude'] != '') { $latitude = esc_attr($paramMMode['latitude']); } else {  $latitude = 47.4; }
        if(isset($paramMMode['longitude']) && $paramMMode['longitude'] != '') { $longitude = esc_attr($paramMMode['longitude']); } else { $longitude = 1.6; }
        if(isset($paramMMode['zoom']) && $paramMMode['zoom'] != '') { $zoom = esc_attr($paramMMode['zoom']); } else { $zoom = 5.5; }
        if(isset($paramMMode['tile']) && $paramMMode['tile'] != '' && is_numeric($paramMMode['tile'])) { $tile = get_mapTile($paramMMode['tile']); } else { $tile = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }
        if(isset($paramMMode['map_icon']) && $paramMMode['map_icon'] != '') { $icon = esc_attr($paramMMode['map_icon']); } else { $icon = 'default'; }

        if(isset($paramMMode["map_icon_size_width"]) && $paramMMode['map_icon_size_width'] != '') { $icon_width = 33; }
        if(isset($paramMMode["map_icon_size_height"]) && $paramMMode['map_icon_size_height'] != '') { $icon_height = 44; }

        $xmlLocationPoint = '';
        $xmlEventsPoint = '';

        $thumbnail = '';

        $upload_dir = wp_upload_dir();
        $createDirectory = self::em_openstreetmap_folder_uploads(sanitize_text_field($type));
        
        if( $type == 'categories' ) {
            // Nom du fichier JS - Carte des catégories
            $pathXml = $createDirectory.'/export-'.sanitize_text_field($type).'.js';
        } else {
            // Nom du fichier XML- Carte des événements ou lieux
            $pathXml = $createDirectory.'/xml-export-'.sanitize_text_field($type).'.js';
        }
        if($forceGenerate == 1 && file_exists($pathXml) === TRUE) {
            wp_delete_file($pathXml);
        }

        // pour le formatage
        $order   = array("\r\n", "\n", "\r", "<p>");
        $replace = '<br />';
            
        /** 
         * 
         * Generation des lieux
         * 
         * */
        if( $type == 'location' && (file_exists($pathXml) === FALSE || $forceGenerate == 1) ) {

            $EM_Locations = EM_Locations::get();

            foreach ($EM_Locations as $key => $EM_Location) {
    
                if( isset($EM_Location->location_latitude) && $EM_Location->location_latitude!='' && $EM_Location->location_status == 1 ) {

                    // Je vais chercher l'icon choisit
                    $mapIcon = get_post_meta(wp_kses_post($EM_Location->post_id), '_location_icon', true);
                    if( isset($mapIcon) && $mapIcon != '') { $icon = $mapIcon; } else { $icon = 'default'; }

                    // Vérifie si il y a un icon custom
                    $customIcon = get_post_meta(wp_kses_post($EM_Location->post_id), '_location_custom_icon', true);
                    
                    // Si il y un icon custom, on vérifie si il a seclectionné Custom Icon
                    if( $icon == 'custom' && (isset($customIcon) && $customIcon != '') ) {
                        $urlIcon = esc_url($customIcon);
                    } else {
                        if( $icon == 'custom' ) { $icon = 'default'; }
                        $urlIcon = esc_url(EMOSM_PLUGIN_URL.'images/markers/'.$icon.'.png');
                    }
                    
                    $address = sanitize_textarea_field($EM_Location->location_address.'<br />'. $EM_Location->location_postcode.' '. $EM_Location->location_town);
                    if( $EM_Location->location_state !='' && (isset($paramMMode["map_location_state"]) && $paramMMode["map_location_state"]==1) ) { $address .= '<br />'. esc_html($EM_Location->location_state); }
                    if( $EM_Location->location_region !='' && (isset($paramMMode["map_location_region"]) && $paramMMode["map_location_region"]==1) ) { $address .= '<br />'. esc_html($EM_Location->location_region); }
                    if( $EM_Location->location_country !='' && (isset($paramMMode["map_location_country"]) && $paramMMode["map_location_country"]==1) ) { $address .= '<br />'. esc_html($EM_Location->location_country); }
                    $textContent = str_replace($order, $replace, $address);
                    $title = str_replace('’', "'", esc_html($EM_Location->location_name));

                    if ( has_post_thumbnail($EM_Location->post_id) ) {
                    $thumbnail = '<div class=\"em-osm-thumbnail\"><a href=\"'.get_the_permalink($EM_Location->post_id).'\">'.addslashes(get_the_post_thumbnail( wp_kses_post($EM_Location->post_id), array(100, 100))).'</a></div>';
                    }                  

                // Construction des bulles d'infos
$xmlLocationPoint .= '['.esc_html($EM_Location->location_latitude).', '.esc_html($EM_Location->location_longitude).', "'.$title.'", "'.get_the_permalink(wp_kses_post($EM_Location->post_id)).'", "'.$thumbnail.'", "'.addslashes($textContent).'", "'.$urlIcon.'"],
';                    
                }
            }

// Build your file contents as a string
$file_locationcontents = 'var addressPoints = [
'.substr($xmlLocationPoint, 0, -2).'
];';
            // Open or create a file (this does it in the same dir as the script)
            $location_file = fopen($pathXml, "w");
                
            // Write the string's contents into that file
            fwrite($location_file, $file_locationcontents);
        
            // Close 'er up
            fclose($location_file);
        
        
        } 
        
        /** 
         * 
         * Generation des evenements
         * 
         * */
        if ( $type == 'events' && (file_exists($pathXml) === FALSE OR $forceGenerate == 1) ) {

            $listEvents = EM_Events::get( array('limit'=>esc_html($limit), 'category' => esc_html($cat), 'scope' => 'future', 'owner'=>false) );
            $events_count = EM_Events::count();
            if( $events_count >= 1 ) {

                foreach ( $listEvents as $event ) {

                    $idThumbnail = get_post_thumbnail_id( wp_kses_post($event->post_id) );
                    $thumbnail = '';
                    if ( has_post_thumbnail($event->post_id) ) {
                    $thumbnail = '<div class=\"em-osm-event-thumbnail\"><a href=\"'.get_the_permalink(wp_kses_post($event->post_id)).'\">'.addslashes(get_the_post_thumbnail( wp_kses_post($event->post_id), array(100, 100))).'</a></div>';
                    }
                    $localised_start_date = date_i18n(get_option('date_format'), $event->start);
                    $localised_end_date = date_i18n(get_option('date_format'), $event->end);

                    if( $localised_end_date != $localised_start_date ) { 
                        $dateEvent = 'Du '.$localised_start_date .' au '.$localised_end_date; 
                    } else if($localised_end_date == $localised_start_date) {
                        $dateEvent = 'Le '.$localised_start_date;
                    }

                    $textContentEvent = esc_html($dateEvent).'<br />';
                    $textContentEvent .= str_replace($order, $replace, get_the_excerpt($event->post_id));
                    $titleEvent = str_replace('’', "'", $event->event_name);

                    $mapIcon = get_post_meta(wp_kses_post($event->post_id), '_location_osm_map_icon', true);
                    if( isset($mapIcon) && $mapIcon != '') { $icon = esc_html($mapIcon); } else { $icon = 'default'; }
                    
                    $urlIconEvent = esc_url(EMOSM_PLUGIN_URL.'images/markers/'.$icon.'.png');

                    // On va cherche les coordonnées du lieu
                    $EM_Location = em_get_location(wp_kses_post($event->location_id));
                    //error_log('ID location:'.$event->location_id);

                    if( isset($icon) && $icon == 'none') {
                        // On va chercher l'icon de la categorie d'evenement
                        $EM_Categories = $event->get_categories();
                        if( $EM_Categories ) {              
                            foreach( $EM_Categories AS $EM_Category){
                                $icon_id = get_term_meta ( wp_kses_post($EM_Category->term_id), 'em-categories-icon-id', true );
                                if( isset($icon_id) && is_numeric($icon_id) && $icon_id !=''){
                                    $urlIconEvent = wp_get_attachment_url($icon_id);
                                }
                            }
                        }
                    }

                    if( isset($EM_Location->location_latitude) && $EM_Location->location_latitude!='' ) {
                    
            // Construction des bulles d'infos
$xmlEventsPoint .= '["'.esc_html($EM_Location->location_latitude).'", "'.esc_html($EM_Location->location_longitude).'", "'.esc_html($titleEvent).'", "'.get_the_permalink($event->post_id).'", "'.$thumbnail.'", "'.addslashes($textContentEvent).'", "'.$urlIconEvent.'"],
';
                    }
                }
            }

// Build your file contents as a string
$file_eventcontents = 'var addressPoints = [
'.substr($xmlEventsPoint, 0, -2).'
];';

            // Open or create a file (this does it in the same dir as the script)
            $events_file = fopen($pathXml, "w");
        
            // Write the string's contents into that file
            fwrite($events_file, $file_eventcontents);
        
            // Close 'er up
            fclose($events_file);

        } 
        
        /** 
         * 
         * Generation des categories
         * 
         * */
        if ( $type == 'categories' && (file_exists($pathXml) === FALSE || $forceGenerate == 1) ) {

            $listEvents = EM_Events::get( array('scope' => 'future', 'owner'=>false) );
            $events_count = EM_Events::count();
            $jsonEventsCatPoint = array();
            $catName = array();
            $catSlug = array();
            $arrayLayers = '';
            if( $events_count >= 1 ) {
        
                foreach ( $listEvents as $event ) {
        
                    // On va cherche les coordonnées du lieu
                    $EM_Location = em_get_location(wp_kses_post($event->location_id));
                    if( isset($EM_Location->location_latitude) && $EM_Location->location_latitude!='' ) {
        
                        $idThumbnail = get_post_thumbnail_id(wp_kses_post($event->post_id));
                        $thumbnail = '';
                        if ( has_post_thumbnail(wp_kses_post($event->post_id)) ) {
                        $thumbnail = '<div class=\"em-osm-cat-thumbnail\"><a href=\"'.get_the_permalink($event->post_id).'\">'.addslashes(get_the_post_thumbnail( wp_kses_post($event->post_id), array(100, 100))).'</a></div>';
                        }
                        $localised_start_date = date_i18n(get_option('date_format'), $event->start);
                        $localised_end_date = date_i18n(get_option('date_format'), $event->end);
        
                        if( $localised_end_date != $localised_start_date ) { 
                            $dateEvent = 'Du '.$localised_start_date .' au '.$localised_end_date; 
                        } else if($localised_end_date == $localised_start_date) {
                            $dateEvent = 'Le '.$localised_start_date;
                        }
        
                        $textContentEvent = esc_html($dateEvent).'<br />';
                        $textContentEvent .= str_replace($order, $replace, get_the_excerpt(wp_kses_post($event->post_id)));
                        $titleEvent = str_replace('’', "'", $event->event_name);
        
                        $mapIcon = get_post_meta($event->post_id, '_location_osm_map_icon', true);
                        if( isset($mapIcon) && $mapIcon != '') { $icon = esc_html($mapIcon); } else { $icon = 'default'; }
                        
                        $urlIconCatEvent = esc_url(EMOSM_PLUGIN_URL.'images/markers/'.$icon.'.png');
                        
                        // On va cherche les coordonnées du lieu
                        $EM_Location = em_get_location(wp_kses_post($event->location_id));
                        // On va chercher l'icon de la categorie d'evenement
                        $EM_Categories = $event->get_categories();
                        if( $EM_Categories ) {
                            
                            foreach( $EM_Categories AS $EM_Category){

                                
                                $icon_id = get_term_meta ( $EM_Category->term_id, 'em-categories-icon-id', true );
                                if( $mapIcon == 'none' && isset($icon_id) && is_numeric($icon_id) && $icon_id !=''){
                                    $urlIconCatEvent = wp_get_attachment_url($icon_id);
                                }

                                $jsonEventsCatPoint[$EM_Category->term_id][] = '{
    "type": "Feature",
    "id": "node/'.wp_kses_post($event->post_id).''.wp_kses_post($EM_Category->term_id).'",
    "properties": {
        "@id": "node/'.wp_kses_post($event->post_id).''.wp_kses_post($EM_Category->term_id).'",
        "amenity": "'.$EM_Category->slug.'",
        "name": "'.sanitize_text_field($titleEvent).'",
        "icon": "'.$urlIconCatEvent.'",
        "text": "'.$textContentEvent.'",
        "thumbnail": "'.$thumbnail.'",
        "link": "'.get_the_permalink($event->post_id).'"
    },
    "geometry": {
        "type": "Point",
        "coordinates": ['.$EM_Location->location_longitude.', '.$EM_Location->location_latitude.']
    }
},
';
                                $catName[$EM_Category->term_id] = $EM_Category->name;
                                $catSlug[$EM_Category->term_id] = $EM_Category->slug;
                                
                            }
                            
                        }
                    }
                }

                
                $pathJson = '';
                
                $catEvents_file = '';
                foreach( $jsonEventsCatPoint as $idCat => $catpoint ) {
                    
                    $markersByCat = '';
                    $pointByCat = '';

                    $arrayLayers .= '"'.$catSlug[$idCat].'",';
                    // Nom du fichier XML pour cette categorie
                    $pathJson = $createDirectory.'/export-'.$catSlug[$idCat].'.json';
                    $pointByCat .= '{
"type": "FeatureCollection",
"generator": "overpass-turbo",
"copyright": "The data included in this document is from www.openstreetmap.org. The data is made available under ODbL.",
"timestamp": "'.date("Y-m-dH:i:s").'",
"features": [
';
                    for($i = 0; $i < count($catpoint); $i++) {
$markersByCat .= $catpoint[$i];
                    }
                    $pointByCat .= substr($markersByCat, 0, -2);
$pointByCat .= ']
}
';               
                    // Open or create a file (this does it in the same dir as the script)
                    $catEvents_file = fopen($pathJson, "w");
                
                    // Write the string's contents into that file
                    fwrite($catEvents_file, $pointByCat);
                
                    // Close 'er up
                    fclose($catEvents_file);
                }


            $scriptFile = '
/* eslint-disable no-undef */
/**
 * control layers outside the map
 */

var osmLink = "<a href=\"http://openstreetmap.org\">OpenStreetMap France</a>",
thunLink = "OpenStreetMap HOT",
esriLink = "Esri WorldStreetMap",
EsriWorldImagery = "Satellite",
CyclOSM = "CyclOSM";

var esriUrl = "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png",
esriAttrib = "",
osmUrl = "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png",
osmAttrib = "&copy; " + osmLink + " Contributors",
landUrl = "https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png",
thunAttrib = "&copy; "+osmLink+" Contributors & "+thunLink,        
EsriWorldImageryUrl = "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
EsriWorldImageryAttrib = "&copy; "+osmLink+" Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community",
CyclOSMUrl = "https://dev.{s}.tile.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png"
CyclOSMAttrib = "&copy; "+osmLink+" <a href=\"https://github.com/cyclosm/cyclosm-cartocss-style/releases\" title=\"CyclOSM - Open Bicycle render\">CyclOSM</a> | Map data: &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors";

var esriMap = L.tileLayer(esriUrl, {attribution: esriAttrib}),
osmMap = L.tileLayer(osmUrl, {attribution: osmAttrib}),
landMap = L.tileLayer(landUrl, {attribution: thunAttrib}),
EsriWorldImagery = L.tileLayer(EsriWorldImageryUrl, {attribution: EsriWorldImageryAttrib}),
CyclOSM = L.tileLayer(CyclOSMUrl, {attribution: CyclOSMAttrib});

var tiles = L.tileLayer("'.$tile.'", {
maxZoom: 18,
attribution: "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors",
id: "mapbox/streets-v11",
tileSize: 512,
zoomOffset: -1
});

const latlng = L.latLng('.$latitude.', '.$longitude.');

var options = {
    maxZoom: 18,
    center: latlng,
    zoom: '.$zoom.',
    layers: [tiles],
    tap:false,
}

var baseLayers = {
    "Custom OpenStreetMap": tiles,
    "OpenStreetMap France": esriMap,
    "OSM Mapnik": osmMap,
    "OpenStreetMap HOT": landMap,        
    "Satellite":EsriWorldImagery,
    "Cycle OSM":CyclOSM
};

// magnification with which the map will start
const zoom = '.$zoom.';
// co-ordinates
const lat = '.$latitude.';
const lng = '.$longitude.';

// calling map
const map = L.map("map", options);


// Used to load and display tile layers on the map
// Most tile servers require attribution, which you can set under `Layer`
L.tileLayer("'.$tile.'", {
  attribution:
    \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\',
    id: \'mapbox/streets-v11\',
    tileSize: 512,
    zoomOffset: -1
}).addTo(map);

// ------------------------------------------------------------

// async function to load geojson
async function fetchData(url) {
  try {
    const response = await fetch(url);
    const data = await response.json();
    return data;
  } catch (err) {
    console.error(err);
  }
}

// fetching data from geojson
const poiLayers = L.layerGroup().addTo(map);

// center map on the clicked marker
function clickZoom(e) {
  map.setView(e.target.getLatLng(), 5.5);
}

let geojsonOpts = {
  pointToLayer: function (feature, latlng) {
    var title = feature.properties.name;
    var urlIcon = feature.properties.icon
    var catIcon = L.icon({
        iconUrl: urlIcon,
        iconSize:     ['.esc_html($icon_width).', '.esc_html($icon_height).'],
        iconAnchor:   ['.(esc_html($icon_width/2)).', '.esc_html($icon_height).'],
        popupAnchor:  [-3, -'.(esc_html($icon_height)).'],
    });
    // create popup contents
    var customPopup = "" + feature.properties.thumbnail + "<div class=\"em-osm-cat-content\"><a href=\"" + feature.properties.link + "\" target=\"_blank\"><h3>" + title + "</h3></a><p>" + feature.properties.text + "</p></div><div class=\"clear\"></div><div class=\"em-osm-cat-readmore\"><a href=\"" + feature.properties.link + "\" target=\"_blank\">'.__( 'Read more', EMOSM_TXT_DOMAIN).'</a></div><br />";
    
    // specify popup options 
    var customOptions = {"maxWidth": "500","className" : "customevent"}

    return L.marker(latlng, {title: title, icon: catIcon})
      .bindPopup(customPopup,customOptions)
      .on("click", clickZoom);
  },
};

const layersContainer = document.querySelector(".catlayers");

const layersButton = "'.__('All Layers', EMOSM_TXT_DOMAIN).'";

function generateButton(name) {
  const id = name === layersButton ? "all-layers" : name;
  const itemname = name.replaceAll("-"," ");
  const templateLayer = `
    <li class="layer-element">
      <label for="${id}" class="contentitem">
        <input type="checkbox" id="${id}" name="item" class="item" value="${name}" checked>
        <span class="checkmark">${itemname}</span>
      </label>
    </li>
  `;

  layersContainer.insertAdjacentHTML("beforeend", templateLayer);
}

generateButton(layersButton);

// add data to geoJSON layer and add to LayerGroup
const arrayLayers = ['.substr($arrayLayers, 0, -1).'];
//const arrayLayers = ["concerts"];
arrayLayers.map((json) => {
  generateButton(json);
  fetchData(`'.str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $createDirectory).'/export-${json}.json`).then((data) => {
    window["layer_" + json] = L.geoJSON(data, geojsonOpts).addTo(map);
  });
});

document.addEventListener("click", (e) => {
  const target = e.target;

  const itemInput = target.closest(".item");

  if (!itemInput) return;

  showHideLayer(target);
});

function showHideLayer(target) {
  if (target.id === "all-layers") {
    arrayLayers.map((json) => {
      checkedType(json, target.checked);
    });
  } else {
    checkedType(target.id, target.checked);
  }

  const checkedBoxes = document.querySelectorAll("input[name=item]:checked");

  document.querySelector("#all-layers").checked =
    checkedBoxes.length <= 3 ? false : true;
}

function checkedType(id, type) {
  map[type ? "addLayer" : "removeLayer"](window["layer_" + id]);

  map.fitBounds(window[["layer_" + id]].getBounds(), { padding: [50, 50] });

  document.querySelector(`#${id}`).checked = type;
}

';

                // Open or create a file (this does it in the same dir as the script)
                $catEvents_scriptfile = fopen($pathXml, "w");
                                
                // Write the string's contents into that file
                fwrite($catEvents_scriptfile, $scriptFile);

                // Close 'er up
                fclose($catEvents_scriptfile);

            }  
        }

        return $pathXml;

    }
       
}