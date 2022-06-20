<?php

defined( 'ABSPATH' )
	or die( 'No direct load ! ' );


//Meta Box pour afficher les infos
add_action('admin_menu','em_openstreetmap_init_metaboxes');
function em_openstreetmap_init_metaboxes() {
    
    global $post, $EM_Location, $EM_Event;

    //on utilise la fonction add_metabox() pour initialiser une metabox
    add_meta_box('em_openstreetmap_settings_infos', __('Settings OpenStreetMap', EMOSM_TXT_DOMAIN), 'em_openstreetmap_settings', 'event', 'normal', 'high');
    add_meta_box('em_openstreetmap_location_infos', __('Location OpenStreetMap', EMOSM_TXT_DOMAIN), 'em_openstreetmap_location', 'location', 'normal', 'high');
    
}
// Meta box EVENT
function em_openstreetmap_settings($post) {

    $mapHeight = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_height', true);
    $mapLayer = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_layer', true);
    $mapZoom = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_zoom', true);
    $mapIcon = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_icon', true);
    if(isset($mapLayer) && !is_numeric($mapLayer)) { $mapLayer=1;}
?>
    <table>
        <tr>
            <td><strong><?php _e('Height:', EMOSM_TXT_DOMAIN); ?><strong></td>
            <td><input type="text" style="border: 1px solid #ececec;padding: 0 8px;line-height: 2;min-height: 30px;text-align: center;
" size="4" name="em_openstreetmap_map_height" value="<?php if( isset($mapHeight) && $mapHeight!='' && is_numeric($mapHeight) ) { echo esc_attr($mapHeight); } else { echo '150'; } ?>">px</td>
        </tr>
        <tr>
            <td><strong><?php _e('Tile:', EMOSM_TXT_DOMAIN); ?></strong></td>
            <td><select name="em_openstreetmap_map_layer" style="width:250px;border: 1px solid #ececec;padding: 0 8px;line-height: 2;min-height: 30px;">
                <?php 
                    $tabTile = array(
                        1 => 'OpenStreetMap\'s Standard',
                        2 => 'OpenStreetMap France',
                        3 => 'Humanitarian map style',
                        4 => 'Satellite',
                        5 => 'CyclOSM',
                        6 => 'Custom API MAPBOX'
                    );
                    foreach($tabTile as $valueTile=>$nameTile){
                        $selected = '';
                        if($valueTile == $mapLayer) { $selected = ' selected="selected"'; }
                        echo '<option value="'.esc_html($valueTile).'" '.esc_html($selected).'>'.esc_html($nameTile).'</value>';
                    }
                    ?>
                    
                    <?php ?>
                </select><br />
                <!-- https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoicmVzdGV6Y29ubmVjdGVzIiwiYSI6ImNrcXVtcHd4aDA2MncydXJ5OHdrN242MG4ifQ.yxsWcR17v_epL7t-pcxltw -->
                <input type="text" name="em_openstreetmap_custom_map_layer" placeholder="<?php _e('Enter here API MapBox access token', EMOSM_TXT_DOMAIN); ?>" style="width:100%;" value="<?php if(isset($paramMMode['custom_tile'])) { echo esc_html($paramMMode['custom_tile']); } ?>" />
            </td>
        </tr>
        <tr>
            <td><strong><?php _e('Zoom:', EMOSM_TXT_DOMAIN); ?></strong></td>
            <td><input name="em_openstreetmap_map_zoom" style="border: 1px solid #ececec;padding: 0 8px;line-height: 2;min-height: 30px;text-align: center;
" size="2" value="<?php if( isset($mapZoom) && $mapZoom != '' && is_numeric($mapZoom) ) { echo esc_attr($mapZoom); } else { echo "13"; } ?>"></td>
        </tr>
        <tr>
            <td><strong><?php _e('Icon:', EMOSM_TXT_DOMAIN); ?></strong></td>
            <td>
                <select name="em_openstreetmap_map_icon" style="border: 1px solid #ececec;padding: 0 8px;line-height: 2;min-height: 30px;text-align: left;" >
                    <option value="default" <?php if( isset($mapIcon) && $mapIcon == 'default') { echo 'selected'; } ?>><?php _e('Default Icon', EMOSM_TXT_DOMAIN); ?></option>
                    <option value="none" <?php if( isset($mapIcon) && $mapIcon == 'none') { echo 'selected'; } ?>><?php _e('Use category icon', EMOSM_TXT_DOMAIN); ?></option>
                    <?php //asort($listIcons); 
                        $listIcons = get_list_cons();
                        foreach( $listIcons as $value => $name) { 
                            $selected = '';
                            if( isset($mapIcon) && $mapIcon == $value) { $selected = 'selected'; }
                        ?>
                        <option value="<?php echo esc_html($value); ?>" <?php echo esc_html($selected); ?>><?php echo esc_html($name); ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
     
    
<?php

}
function em_openstreetmap_settings_save( $post_id ) {

    if( isset($_POST['em_openstreetmap_map_height']) && $_POST['em_openstreetmap_map_height']!='' && is_numeric($_POST['em_openstreetmap_map_height']) ) { $mapHeight = esc_html($_POST['em_openstreetmap_map_height']); } else { $mapHeight = 250; }
    update_post_meta($post_id, '_location_osm_map_height', esc_html($mapHeight));
    //if( isset($_POST['em_openstreetmap_map_layer']) && $_POST['em_openstreetmap_map_layer']!='' ) { $mapLayer = esc_url($_POST['em_openstreetmap_map_layer'], array('{', '}', '?') ); } else { $mapLayer = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }
        //error_log('layer:'.esc_url($_POST['em_openstreetmap_map_layer'], array('{', '}', '?') ));
    if( isset($_POST['em_openstreetmap_map_layer']) && $_POST['em_openstreetmap_map_layer']!='' ) {
        update_post_meta($post_id, '_location_osm_map_layer', sanitize_text_field($_POST['em_openstreetmap_map_layer'])); 
    }
    if( isset($_POST['em_openstreetmap_map_zoom']) && $_POST['em_openstreetmap_map_zoom']!='' && is_numeric($_POST['em_openstreetmap_map_zoom']) ) { $mapZoom = esc_html($_POST['em_openstreetmap_map_zoom']); } else { $mapZoom = 13; }
    update_post_meta($post_id, '_location_osm_map_zoom', sanitize_text_field($mapZoom));
    if( isset($_POST['em_openstreetmap_map_icon']) && $_POST['em_openstreetmap_map_icon']!='' ) { $mapIcon = esc_html($_POST['em_openstreetmap_map_icon']); } else { $mapIcon = 'default'; }
    update_post_meta(wp_kses_post($post_id), '_location_osm_map_icon', $mapIcon);
    $genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate('events', '', 0, 1);
    $genereCategorie = EM_Openstreetmap_Class::em_openstreetmap_generate('categories', '', 0, 1);
}
add_action( 'save_post', 'em_openstreetmap_settings_save' );

function em_openstreetmap_location($post) {

    global $post, $EM_Location, $EM_Event;

    $mapHeight = '250';
    $mapZoom = 16;
    $marker_icon_width = 33;
    $marker_icon_height = 44;

    $customIcon = get_post_meta(wp_kses_post($post->ID), '_location_custom_icon', true);
    $mapIcon = get_post_meta(wp_kses_post($post->ID), '_location_icon', true);

    if( isset($EM_Event->location_id) && $EM_Event->location_id !== 0 ){

        $EM_Location = $EM_Event->get_location();
        $latitude = $EM_Location->location_latitude; 
        $longitude = $EM_Location->location_longitude;        

    } else if( isset($EM_Location->location_id) && $EM_Location->location_id !== 0 ) {

        $EM_Location = em_get_location($EM_Location->location_id);
        $latitude = $EM_Location->location_latitude; 
        $longitude = $EM_Location->location_longitude;

    } else {

        $EM_Location = new EM_Location(wp_kses_post($post->ID), 'post_id');
        $latitude = '47.4';
        $longitude = '1.6';
        $mapZoom = 5.5;
        
    }

    if( isset($latitude) && $latitude == 0 || $latitude=='' ) { $latitude = '47.4'; $mapZoom = 5.5; }
    if( isset($longitude) && $longitude == 0 || $longitude=='' ) { $longitude = '1.6'; }
    $mapHeight = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_height', true);
    if( isset($mapHeight) && ($mapHeight == 0 || $mapHeight=='') && is_numeric($mapHeight)  ) { $mapHeight = intval($mapHeight); }


    if( is_object($EM_Location) && !$EM_Location->can_manage('edit_locations','edit_others_locations') ){
        ?>
        <div class="wrap"><h2><?php esc_html_e('Unauthorized Access','events-manager'); ?></h2><p><?php echo sprintf(__('You do not have the rights to manage this %s.','events-manager'),__('location','events-manager')); ?></p></div>
        <?php
        return false;
    } else {

        //echo 'COORD: LAT:'.$latitude.' - LONG '.$longitude.'';
        if( isset($customIcon) && $customIcon != '' &&  $mapIcon == 'custom') {
            $urlIcon = $customIcon;
        } else {
            if( $mapIcon == 'custom' || empty($mapIcon) ) { $mapIcon = 'default'; }
            $urlIcon = esc_url(EMOSM_PLUGIN_URL.'images/markers/'.esc_html($mapIcon).'.png');
        }

        if( ini_get('allow_url_fopen') ) {
            list($marker_icon_width, $marker_icon_height, $type, $attr) = getimagesize($urlIcon);
        } else {
            $marker_icon_width = 33;
            $marker_icon_height = 44;
        } 

        
    ?>
    <style>
    #map {width:100%;border: 2px solid #ddd;height:<?php echo esc_html($mapHeight); ?>px;margin: 0;border-radius: 5px;}
    #map {overflow:hidden;padding-bottom:56.25%;position:relative;height:0;}
    .address {padding:2px;}
    .address:focus, .address:hover {background-color:#848838;color:#ffffff;font-size:16px;}
    #location-name {margin: 2px 0 7px;padding: 6px 5px;width: 60%;}
    </style>
    <div id="em_openstreetmap-location-data">
        <div id="em_openstreetmap-location-form" style="float:left;width:50%;">
            
            <form>
                <input type="hidden" name="em_location_id" value="<?php echo wp_kses_post($EM_Location->location_id); ?>">
                <input type="hidden" name="em_location_latitude" id="lat" value="<?php echo esc_html($latitude); ?>">
                <input type="hidden" name="em_location_longitude" id="lon" value="<?php echo esc_html($longitude); ?>">
                <div id="search">
                    <h4><?php _e('Search a place with its coordinates with OpenStreeMap, drag the marker for more precision.', EMOSM_TXT_DOMAIN); ?></h4>
                    <input type="text" class="input" name="addr" value="<?php if( isset($_GET['addr']) ) { echo esc_html(str_replace('+', ' ', $_GET['addr'])); } ?>" id="addr" />
                    <button type="button" class="btn btn-primary" onclick="addr_search();"><?php _e('SEARCH', EMOSM_TXT_DOMAIN); ?></button>
                    <div id="results"></div>
                    <div id="coord" style="margin-top: 25px;"></div>
                </div>
                <h4><?php _e('Map Options', EMOSM_TXT_DOMAIN); ?></h4>
                <!--<?php _e('Height:', EMOSM_TXT_DOMAIN); ?> <input type="text" size="4" name="em_openstreetmap_map_height" value="<?php echo esc_html($mapHeight); ?>">px<br />-->
                <?php _e('Icon:', EMOSM_TXT_DOMAIN); ?>
                <select name="em_location_icon" style="border: 1px solid #ececec;padding: 0 8px;line-height: 2;min-height: 30px;text-align: left;" >
                    <option value="default" <?php if( isset($mapIcon) && $mapIcon == 'default') { echo 'selected'; } ?>><?php _e('Default Icon', EMOSM_TXT_DOMAIN); ?></option>
                    <option value="custom" <?php if( isset($mapIcon) && $mapIcon == 'custom') { echo 'selected'; } ?>><?php _e('Custom Icon', EMOSM_TXT_DOMAIN); ?></option>
                    <?php //asort($listIcons); 
                        $listIcons = get_list_cons();
                        foreach( $listIcons as $value => $name) { 
                            $selected = '';
                            if( isset($mapIcon) && $mapIcon == $value) { $selected = 'selected'; }
                        ?>
                        <option value="<?php echo esc_html($value); ?>" <?php echo esc_html($selected); ?>><?php echo esc_html($name); ?></option>
                    <?php } ?>
                </select><p><i><?php _e('Select "Custom Icon" for use a custom icon', EMOSM_TXT_DOMAIN); ?></i></p><br />
                
                <table>
                    <tr>
                        <td><?php _e('Your Custom Icon:', EMOSM_TXT_DOMAIN); ?></td>
                        <td><?php if( isset($customIcon) && $customIcon != '' ) { ?><img src="<?php _e(esc_url($customIcon)); ?>" width="33"><?php } ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input id="upload_icon" size="55%" name="em_location_custom_icon" value="<?php if( isset($customIcon) && $customIcon!='' ) { echo esc_url($customIcon); } ?>" type="text" /> <button type="button" id="upload_icon_button" class="btn btn-primary" OnClick="this.blur();"><?php _e('Uploader', EMOSM_TXT_DOMAIN); ?></button></td>
                    </tr>
                </table>
                
            </form>
            
        </div>
        <div id="em_openstreetmap-location-map" style="float:left;width:50%;">  
            <div id="map"></div>
        </div>
    </div>
    <div style="clear:both;">&nbsp;</div>

    <script type="text/javascript">

        var osmLink = '<a href="http://openstreetmap.org">OpenStreetMap France</a>',
                thunLink = 'OpenStreetMap HOT',
                esriLink = 'Esri WorldStreetMap',
                EsriWorldImagery = 'Satellite',
                CyclOSM = 'CyclOSM';
   
        var esriUrl = 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
            esriAttrib = '',
            osmUrl = 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
            osmAttrib = '&copy; ' + osmLink + ' Contributors',
            landUrl = 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
            thunAttrib = '&copy; '+osmLink+' Contributors & '+thunLink,        
            EsriWorldImageryUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            EsriWorldImageryAttrib = '&copy; '+osmLink+' Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
            CyclOSMUrl = 'https://dev.{s}.tile.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png'
            CyclOSMAttrib = '&copy; '+osmLink+' <a href="https://github.com/cyclosm/cyclosm-cartocss-style/releases" title="CyclOSM - Open Bicycle render">CyclOSM</a> | Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

        var esriMap = L.tileLayer(esriUrl, {attribution: esriAttrib}),
            osmMap = L.tileLayer(osmUrl, {attribution: osmAttrib}),
            landMap = L.tileLayer(landUrl, {attribution: thunAttrib}),
            EsriWorldImagery = L.tileLayer(EsriWorldImageryUrl, {attribution: EsriWorldImageryAttrib}),
            CyclOSM = L.tileLayer(CyclOSMUrl, {attribution: CyclOSMAttrib});

        // New York
        var startlat = <?php echo esc_html($latitude); ?>;
        var startlon = <?php echo esc_html($longitude); ?>;

        var options = {
            center: [startlat, startlon],
            zoom: <?php echo intval($mapZoom); ?>,
            layers: [esriMap],
        }

        document.getElementById('lat').value = startlat;
        document.getElementById('lon').value = startlon;

        var map = L.map('map', options);
        var nzoom = 11;
        
        var baseLayers = {
            "Esri": esriMap,
            "OSM Mapnik": osmMap,
            "OpenStreetMap HOT": landMap,        
            "Satellite":EsriWorldImagery,
            "Cycle OSM":CyclOSM
        };

        var LeafIcon = L.Icon.extend({
            options: {
                <?php if($marker_icon_width==$marker_icon_height) {  ?>
                iconSize:     [60, 60],
                <?php } else { ?>
                iconSize:     [<?php echo esc_html($marker_icon_width); ?>, <?php echo esc_html($marker_icon_height); ?>],
                iconAnchor:   [<?php echo esc_html($marker_icon_width)/2; ?>, <?php echo esc_html($marker_icon_height); ?>],
                popupAnchor:  [0, -<?php echo esc_html($marker_icon_height); ?>],
                <?php } ?>
            }
        });
        var rcIcon = new LeafIcon({iconUrl: '<?php echo esc_url($urlIcon); ?>'});
        L.control.layers(baseLayers).addTo(map);

        var myMarker = L.marker([startlat, startlon], {title: "<?php _e('Drag marker for more precision.', EMOSM_TXT_DOMAIN); ?>", alt: "<?php _e('Drag marker for more precision.', EMOSM_TXT_DOMAIN); ?>", draggable: true, icon: rcIcon}).addTo(map).on('dragend', function() {
            var lat = myMarker.getLatLng().lat.toFixed(8);
            var lon = myMarker.getLatLng().lng.toFixed(8);

            //var inp = document.getElementById("addr");
            
            var czoom = map.getZoom();
            if(czoom < 16) { nzoom = czoom + 2; }
            if(nzoom > 18) { nzoom = 18; }
            if(czoom != 18) { map.setView([lat,lon], nzoom); } else { map.setView([lat,lon]); }
            document.getElementById('lat').value = lat;
            document.getElementById('lon').value = lon;
            myMarker.bindPopup("<?php _e('Drag marker for more precision.', EMOSM_TXT_DOMAIN); ?>").openPopup();
        });
        map.scrollWheelZoom.disable();

        function chooseAddr(lat1, lng1) {
            console.log("Coordon: " + lat1 + ", " + lng1);
            myMarker.closePopup();
            map.setView([lat1, lng1],18);
            myMarker.setLatLng([lat1, lng1]);
            lat = lat1.toFixed(8);
            lon = lng1.toFixed(8);

            document.getElementById('lat').value = lat;
            document.getElementById('lon').value = lon;

            myMarker.bindPopup("<?php _e('Drag marker for more precision.', EMOSM_TXT_DOMAIN); ?>").openPopup();
        }

        function myFunction(arr) {

            var out = "<br /><h1 class=\"entry-title main_title\"><?php _e('Choose the best answer:', EMOSM_TXT_DOMAIN); ?></h1>";
            var i;

            if(arr.length > 0) {
                for(i = 0; i < arr.length; i++) {
                    //console.log("Coordon: " + arr[i].lat + ", " + arr[i].lon + ", " + arr[i].display_name);
                    out += "<div class='address' title='<?php _e('Display this location and these coordinates', EMOSM_TXT_DOMAIN); ?>' onclick='chooseAddr(" + arr[i].lat + ", " + arr[i].lon + ");return false;' style='cursor: pointer;'>&rarr; " + arr[i].display_name + "</div>";
                }
                document.getElementById('results').innerHTML = out;
            } else {
                document.getElementById('results').innerHTML = "<?php _e('Sorry... not found', EMOSM_TXT_DOMAIN); ?>";
            }

        }
        
        if( document.getElementById("addr").value ) {
            addr_search();
        }

        function addr_search() {

            var inp = document.getElementById("addr");
        
            var xmlhttp = new XMLHttpRequest();
            var url = "https://nominatim.openstreetmap.org/search?format=json&limit=5&q=" + inp.value;
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var myArr = JSON.parse(this.responseText);
                    myFunction(myArr);
                }
            };
            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        }

    </script>
<?php
    }
}

function em_openstreetmap_metabox_save( $post_id ) {

    global $wpdb;
    $EM_Location = new EM_Location(wp_kses_post($post_id), 'post_id');

    if( isset($_POST['em_location_id']) && is_numeric($_POST['em_location_id']) ) { $idLocation = (int) $_POST['em_location_id']; } else { $idLocation = wp_kses_post($EM_Location->location_id); }

    if( isset($_POST['em_location_icon']) ) { 
        update_post_meta(wp_kses_post($post_id), '_location_icon', sanitize_text_field($_POST['em_location_icon']));
    }
    if( isset($_POST['em_location_custom_icon']) ) { 
        update_post_meta(wp_kses_post($post_id), '_location_custom_icon', sanitize_text_field($_POST['em_location_custom_icon']));
    }

    if( isset($_POST['em_location_latitude']) && isset($_POST['em_location_longitude']) ) {

        update_post_meta(wp_kses_post($post_id), '_location_latitude', sanitize_text_field($_POST['em_location_latitude']));
        update_post_meta(wp_kses_post($post_id), '_location_longitude', sanitize_text_field($_POST['em_location_longitude']));
        $genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate('location', '', 0, 1);
        $genereCategorie = EM_Openstreetmap_Class::em_openstreetmap_generate('categories', '', 0, 1);
        $data = array(
            'location_latitude' => sanitize_text_field($_POST['em_location_latitude']),
            'location_longitude' => sanitize_text_field($_POST['em_location_longitude']),
        );
        $where = array( 
            'location_id' => $idLocation,
        );
        $result = $wpdb->update( $wpdb->prefix.'em_locations', $data, $where );
    }      
    

}
add_action( 'save_post', 'em_openstreetmap_metabox_save' );

function get_list_cons() {

    return array(
        "accountancy" => __('Accountancy', EMOSM_TXT_DOMAIN),
        "arts-crafts" => __('Arts Crafts', EMOSM_TXT_DOMAIN), 
        "astrology" => __('Astrology', EMOSM_TXT_DOMAIN), 
        "automotive" => __('Automotive', EMOSM_TXT_DOMAIN), 
        "bars" => __('Bars', EMOSM_TXT_DOMAIN), 
        "birds" => __('Birds', EMOSM_TXT_DOMAIN), 
        "books-media" => __('Books Media', EMOSM_TXT_DOMAIN), 
        "breakfast-n-brunch" => __('Breakfast & Brunch', EMOSM_TXT_DOMAIN), 
        "business" => __('Business', EMOSM_TXT_DOMAIN), 
        "cake-shop" => __('Cake Shop', EMOSM_TXT_DOMAIN), 
        "clothings" => __('Clothings', EMOSM_TXT_DOMAIN), 
        "clubs" => __('Clubs', EMOSM_TXT_DOMAIN), 
        "coffee-n-tea" => __('Coffee & Tea', EMOSM_TXT_DOMAIN), 
        "commercial-places" => __('Commercial Places', EMOSM_TXT_DOMAIN), 
        "community" => __('Community', EMOSM_TXT_DOMAIN), 
        "computers" => __('Computers', EMOSM_TXT_DOMAIN), 
        "concerts" => __('Concerts', EMOSM_TXT_DOMAIN), 
        "cookbooks" => __('Cookbooks', EMOSM_TXT_DOMAIN), 
        "dance-clubs" => __('Dance Clubs', EMOSM_TXT_DOMAIN), 
        "dental" => __('Dental', EMOSM_TXT_DOMAIN), 
        "doctors" => __('Doctors', EMOSM_TXT_DOMAIN), 
        "education" => __('Education', EMOSM_TXT_DOMAIN), 
        "electronics" => __('Electronics', EMOSM_TXT_DOMAIN), 
        "employment" => __('Employment', EMOSM_TXT_DOMAIN), 
        "engineering" => __('Engineering', EMOSM_TXT_DOMAIN), 
        "entertainment" => __('Entertainment', EMOSM_TXT_DOMAIN), 
        "event" => __('Event', EMOSM_TXT_DOMAIN), 
        "exhibitions" => __('Exhibitions', EMOSM_TXT_DOMAIN), 
        "fashion" => __('Fashion', EMOSM_TXT_DOMAIN), 
        "festivals" => __('Festivals', EMOSM_TXT_DOMAIN), 
        "financial-services" => __('Financial Services', EMOSM_TXT_DOMAIN), 
        "food" => __('Food', EMOSM_TXT_DOMAIN), 
        "furniture-stores" => __('Furniture Stores', EMOSM_TXT_DOMAIN), 
        "games" => __('Games', EMOSM_TXT_DOMAIN), 
        "gifts-flowers" => __('Gifts Flowers', EMOSM_TXT_DOMAIN), 
        "government" => __('Government', EMOSM_TXT_DOMAIN), 
        "halloween" => __('Halloween', EMOSM_TXT_DOMAIN), 
        "health-medical" => __('Health Medical', EMOSM_TXT_DOMAIN), 
        "home-services" => __('Home Services', EMOSM_TXT_DOMAIN), 
        "hotels" => __('Hotels', EMOSM_TXT_DOMAIN), 
        "industries" => __('Industries', EMOSM_TXT_DOMAIN), 
        "internet" => __('Internet', EMOSM_TXT_DOMAIN), 
        "jewelry" => __('Jewelry', EMOSM_TXT_DOMAIN), 
        "jobs" => __('Jobs', EMOSM_TXT_DOMAIN), 
        "karaoke" => __('Karaoke', EMOSM_TXT_DOMAIN), 
        "law" => __('Law', EMOSM_TXT_DOMAIN), 
        "lawn-garden" => __('Lawn Garden', EMOSM_TXT_DOMAIN), 
        "libraries" => __('Libraries', EMOSM_TXT_DOMAIN), 
        "local-services" => __('Local Services', EMOSM_TXT_DOMAIN), 
        "lounges" => __('Lounges', EMOSM_TXT_DOMAIN), 
        "magazines" => __('Magazines', EMOSM_TXT_DOMAIN), 
        "manufacturing" => __('Manufacturing', EMOSM_TXT_DOMAIN), 
        "marker-new1_12" => __('Marker New', EMOSM_TXT_DOMAIN), 
        "mass-media" => __('Mass Media', EMOSM_TXT_DOMAIN), 
        "massage-therapy" => __('Massage Therapy', EMOSM_TXT_DOMAIN), 
        "matrimonial" => __('Matrimonial', EMOSM_TXT_DOMAIN), 
        "medical" => __('Medical', EMOSM_TXT_DOMAIN), 
        "meetups" => __('Meetups', EMOSM_TXT_DOMAIN), 
        "miscellaneous-for-sale" => __('Miscellaneous For Sale', EMOSM_TXT_DOMAIN), 
        "mobile-phones" => __('Mobile Phones', EMOSM_TXT_DOMAIN), 
        "movies" => __('Movies', EMOSM_TXT_DOMAIN), 
        "museums" => __('Museums', EMOSM_TXT_DOMAIN), 
        "musical-instruments" => __('Musical Instruments', EMOSM_TXT_DOMAIN), 
        "musical" => __('Musical', EMOSM_TXT_DOMAIN), 
        "nightlife" => __('Nightlife', EMOSM_TXT_DOMAIN), 
        "parks" => __('Parks', EMOSM_TXT_DOMAIN), 
        "parties" => __('Parties', EMOSM_TXT_DOMAIN), 
        "pets" => __('Pets', EMOSM_TXT_DOMAIN), 
        "photography" => __('Photography', EMOSM_TXT_DOMAIN), 
        "pizza" => __('Pizza', EMOSM_TXT_DOMAIN), 
        "places" => __('Places', EMOSM_TXT_DOMAIN), 
        "play-schools" => __('Play Schools', EMOSM_TXT_DOMAIN), 
        "playgrounds" => __('Playgrounds', EMOSM_TXT_DOMAIN), 
        "pool-halls" => __('Pool Halls', EMOSM_TXT_DOMAIN), 
        "printing-graphic-arts" => __('Printing Graphic Arts', EMOSM_TXT_DOMAIN), 
        "professional" => __('Professional', EMOSM_TXT_DOMAIN), 
        "real-estate" => __('Real Estate', EMOSM_TXT_DOMAIN), 
        "religious-organizations" => __('Religious Organizations', EMOSM_TXT_DOMAIN), 
        "residential-places" => __('Residential Places', EMOSM_TXT_DOMAIN), 
        "restaurants" => __('Restaurants', EMOSM_TXT_DOMAIN), 
        "retail-stores" => __('Retail Stores', EMOSM_TXT_DOMAIN), 
        "saloon" => __('Saloon', EMOSM_TXT_DOMAIN), 
        "schools" => __('Schools', EMOSM_TXT_DOMAIN), 
        "science" => __('Science', EMOSM_TXT_DOMAIN), 
        "shopping" => __('Shopping', EMOSM_TXT_DOMAIN), 
        "sporting-goods" => __('Sporting Goods', EMOSM_TXT_DOMAIN), 
        "sports" => __('Sports', EMOSM_TXT_DOMAIN), 
        "swimming-pools" => __('Swimming Pools', EMOSM_TXT_DOMAIN), 
        "telemarketing" => __('Telemarketing', EMOSM_TXT_DOMAIN), 
        "tickets" => __('Tickets', EMOSM_TXT_DOMAIN), 
        "tiffin-services" => __('Tiffin Services', EMOSM_TXT_DOMAIN), 
        "tires-accessories" => __('Tires Accessories', EMOSM_TXT_DOMAIN), 
        "tools-hardware" => __('Tools Hardware', EMOSM_TXT_DOMAIN), 
        "tours" => __('Tours', EMOSM_TXT_DOMAIN), 
        "toys-store" => __('Toys Store', EMOSM_TXT_DOMAIN), 
        "transport" => __('Transport', EMOSM_TXT_DOMAIN), 
        "travel" => __('Travel', EMOSM_TXT_DOMAIN), 
        "tutors" => __('Tutors', EMOSM_TXT_DOMAIN), 
        "vacant-land" => __('Vacant Land', EMOSM_TXT_DOMAIN)
    );
}