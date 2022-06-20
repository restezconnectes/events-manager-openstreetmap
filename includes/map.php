<?php

defined( 'ABSPATH' )
	or die( 'No direct load ! ' );

/**
 * Add a custom placeholder to display the openstreetmap of this event.
 * https://tomik23.github.io/leaflet-examples/#62.count-markers
 * @param string $replace
 * @param EM_Event $EM_Event
 * @param string $result
 * @return string
 */
function em_openstreetmap_placeholders($replace, $EM_Event, $result){

	if( $result == '#_OPENSTREETMAP' ){
        
        global $post, $EM_Event, $EM_Location;

        if( isset($EM_Event->location_id) && $EM_Event->location_id !== 0 ){
            $EM_Location = $EM_Event->get_location();
        }        

        $latitude = get_post_meta(wp_kses_post($post->ID), '_location_latitude', true);
        $longitude = get_post_meta(wp_kses_post($post->ID), '_location_longitude', true);

        if( isset($EM_Location->location_latitude) && $EM_Location->location_latitude!='' ) {
            
            if( isset($EM_Location->location_latitude) && $EM_Location->location_latitude!="" ) { 
            $latitude = $EM_Location->location_latitude; } else { $latitude='47.6'; }

            if( isset($EM_Location->location_longitude) && $EM_Location->location_longitude!="" ) { 
            $longitude = $EM_Location->location_longitude; } else { $longitude='1.6'; }
            if( isset($EM_Location->location_name) && $EM_Location->location_name!="" ) { 
            $name = $EM_Location->location_name; } else { $name = ''; }
            if( isset($EM_Location->location_postcode) && $EM_Location->location_postcode!="" ) { 
            $postcode = $EM_Location->location_postcode; } else { $postcode = ''; }
            if( isset($EM_Location->location_address) && $EM_Location->location_address!="" ) { 
            $address = $EM_Location->location_address; } else { $address = ''; }
            if( isset($EM_Location->location_town) && $EM_Location->location_town!="" ) { 
            $town = $EM_Location->location_town; } else { $town = ''; }


            $mapHeight = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_height', true);
            if( isset($mapHeight) && $mapHeight!='' ) { $height = esc_attr($mapHeight); } else { $height = '250'; }

            $mapLayer = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_layer', true);
            if( isset($mapLayer) && $mapLayer!='' ) { $layer = esc_html($mapLayer); } else { $layer = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }

            $mapZoom = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_zoom', true);
            if( isset($mapZoom) && $mapZoom!='' ) { $zoom = esc_html($mapZoom); } else { $zoom = 13; }

            $mapIcon = get_post_meta(wp_kses_post($post->ID), '_location_osm_map_icon', true);
            if( (isset($mapIcon) && $mapIcon=="") || empty($mapIcon) ) { $mapIcon = 'default'; }
            $urlIcon = esc_url(EMOSM_PLUGIN_URL.'images/markers/'.esc_html($mapIcon).'.png');
            
            // On va chercher l'icon de la categorie d'evenement
            if( isset($mapIcon) && $mapIcon=="none" ) { 
                $EM_Categories = $EM_Event->get_categories();
                if( $EM_Categories ) {
                    foreach( $EM_Categories AS $EM_Category){
                        $iconId = get_term_meta (esc_html($EM_Category->term_id), 'em-categories-icon-id', true);
                        if( $iconId !='' && is_numeric($iconId) ) {
                            $urlIcon = wp_get_attachment_url($iconId);
                        } else {
                            $urlIcon = esc_url(EMOSM_PLUGIN_URL.'images/markers/default.png');
                            
                        }
                    }
                }
            }
            
            if( ini_get('allow_url_fopen') ) {
                list($marker_icon_width, $marker_icon_height, $type, $attr) = getimagesize($urlIcon);
            } else {
                $marker_icon_width = 33;
                $marker_icon_height = 44;
            }
            if( isset($marker_icon_width) && $marker_icon_width=="") { $marker_icon_width = 33; }
            if( isset($marker_icon_height) && $marker_icon_height=="") { $marker_icon_height = 44; }
            if ( $urlIcon == '' ) { $urlIcon = esc_url(EMOSM_PLUGIN_URL.'images/markers/default.png'); }

            $EM_Location = em_get_location($EM_Event->location_id);

            $replace = '<!-- '.$urlIcon.'-->
            <div id="event-map" style="height: '.$height.'px!important;"></div>';
            $replace .= "<script>
                var options = {
                    zoom: ".$zoom.",
                    tap:false,
                }
                const map = L.map('event-map', options).setView([".$latitude.", ".$longitude."], ".$zoom.");
                L.tileLayer('".$layer."', {
                    maxZoom: 19,
                    attribution: '&copy; <a href=\"http://osm.org/copyright\">OpenStreetMap</a> contributors',
                    id: 'mapbox/streets-v11',
                    tileSize: 512,
                    zoomOffset: -1
                }
                ).addTo(map);

                var myIcon = L.icon({
                    iconUrl: '".$urlIcon."',
                    iconSize:     [".$marker_icon_width.", ".$marker_icon_height."],
                    iconAnchor:   [".($marker_icon_width/2).", ".$marker_icon_height."],
                    popupAnchor:  [0, -".($marker_icon_height)."],
                });


                function setLeafletMarker() {
                    L.marker([".$latitude.", ".$longitude."], { icon: myIcon })
                        .addTo(map)
                        .bindPopup('<div class=\"em-osm-single-content\"><h3>".$name."</h3><p>".$address."<br />".$postcode." ".$town."</p></div><div class=\"clear\"></div><div class=\"em-osm-single-readmore\"><a href=\"".get_the_permalink( $EM_Location->post_id )."\" target=\"_blank\">".__( 'Read more', EMOSM_TXT_DOMAIN)."</a></div><br />');
                };
                jQuery(document).ready(function () {
                    setLeafletMarker()
                });

            </script>
            ";
                    
         
            } else {
                $replace = '';
            }
	}

	return $replace;
}
add_filter('em_event_output_placeholder','em_openstreetmap_placeholders',1,3);
add_filter('em_location_output_placeholder','em_openstreetmap_placeholders',1,4);

function em_openstreetmap_map( $atts ) {

    global $EM_Location;
    global $post;

    // Récupère les paramètres sauvegardés
    if(get_option('em_openstreetmap_setting')) { extract(get_option('em_openstreetmap_setting')); }
    $paramMMode = get_option('em_openstreetmap_setting');

    if(isset($paramMMode['latitude']) && $paramMMode['latitude'] != '') { $latitude = esc_attr($paramMMode['latitude']); } else {  $latitude = 47.4; }
    if(isset($paramMMode['longitude']) && $paramMMode['longitude'] != '') { $longitude = esc_attr($paramMMode['longitude']); } else { $longitude = 1.6; }
    if(isset($paramMMode['zoom']) && $paramMMode['zoom'] != '') { $zoom = esc_attr($paramMMode['zoom']); } else { $zoom = 5.5; }
    if(isset($paramMMode['tile']) && $paramMMode['tile'] != '' && is_numeric($paramMMode['tile'])) { $tile = get_mapTile($paramMMode['tile']); } else { $tile = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }
    if( isset($paramMMode['map_icon']) && $paramMMode['map_icon'] != '') { $icon = esc_html($paramMMode['map_icon']); } else { $icon = 'default'; }

    if( isset($paramMMode["map_icon_size_width"]) && $paramMMode['map_icon_size_width'] != '' ) { $icon_width = 33; }
    if( isset($paramMMode["map_icon_size_height"]) && $paramMMode['map_icon_size_height'] != '' ) { $icon_height = 44; }

    // Attributes
    extract( shortcode_atts(
        array(
            'type' => 'location',
            'name' => '',
            'thumbnails' => 1,
            'state' => 0,
            'region' => 0,
            'country' => 0,
            'baseLayers' => 1,
            'search' => 1,
            'icon_url' => ''.esc_url(EMOSM_URL.'images/markers/'.$icon.'.png').'',
            'icon_width' => 33,
            'icon_height' => 44,
            'readmore' => __( 'Read more', EMOSM_TXT_DOMAIN),
            'legend_location' => 1,
            'legend_events' => 1,
            'cat' => '',
            'limit' => 0,
            'height' => 450,
            'map_latitude' => '',
            'map_longitude' => '',
            'home_button' => 1,
            'mini_map' => 1
        ), $atts )
    );

    $upload_dir = wp_upload_dir();
    $genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate($type, $cat, $limit);

    $nameMap = '';
    if( isset($name) && $name!='') {
        $nameMap = '_'.esc_html($name);
        $map = "<style>#map".$nameMap." {height:".esc_html($height)."px;}</style>";
    }
    $map = "<div id=\"map".$nameMap."\">
       <!-- Ici s'affichera la carte -->
    </div>";

    $map .= "<script type=\"text/javascript\">
        
        var osmLink = '<a href=\"http://openstreetmap.org\">OpenStreetMap France</a>',
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
            CyclOSMAttrib = '&copy; '+osmLink+' <a href=\"https://github.com/cyclosm/cyclosm-cartocss-style/releases\" title=\"CyclOSM - Open Bicycle render\">CyclOSM</a> | Map data: &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors';

        var esriMap = L.tileLayer(esriUrl, {attribution: esriAttrib}),
            osmMap = L.tileLayer(osmUrl, {attribution: osmAttrib}),
            landMap = L.tileLayer(landUrl, {attribution: thunAttrib}),
            EsriWorldImagery = L.tileLayer(EsriWorldImageryUrl, {attribution: EsriWorldImageryAttrib}),
            CyclOSM = L.tileLayer(CyclOSMUrl, {attribution: CyclOSMAttrib});

		var tiles = L.tileLayer('".$tile."', {
				maxZoom: 18,
				attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors',
                id: 'mapbox/streets-v11',
                tileSize: 512,
                zoomOffset: -1
			}),
            
            ";

        if( isset($map_latitude) && $map_latitude!='' && $map_longitude!='' ) {
    $map .= "latlng = L.latLng(".esc_attr($map_latitude).", ".esc_attr($map_longitude)."); ";        
        } else {
    $map .= "latlng = L.latLng(".esc_attr($latitude).", ".esc_attr($longitude).");";
        }
    $map .= "
        var options = {
            maxZoom: 18,
            center: latlng,
            zoom: ".intval($zoom).",
            layers: [tiles],
            tap:false,
        }
        var map = L.map('map".$nameMap."', options);

        var baseLayers = {
            'Custom OpenStreetMap': tiles,
            'OpenStreetMap France': esriMap,
            'OSM Mapnik': osmMap,
            'OpenStreetMap HOT': landMap,        
            'Satellite':EsriWorldImagery,
            'Cycle OSM':CyclOSM
        };

";

if( $search == 1) {
    $map .= "
        /// ------ GEOCODER
        var IconSearch = L.icon({
            iconUrl: '".esc_url(EMOSM_URL.'images/iconsearch.png')."',
            iconSize:     [32, 48],
            iconAnchor:   [16, 48],
            popupAnchor:  [-3, -48],
        });
        
        var optionsSearch = {
            placeholder: '".__('Search for places or addresses', EMOSM_TXT_DOMAIN)."',
            //position: 'topright'
        }
                
        // create the geocoding control and add it to the map
        var searchControl = L.esri.Geocoding.geosearch(optionsSearch).addTo(map);
    
        // create an empty layer group to store the results and add it to the map
        var results = L.layerGroup().addTo(map);
    
        // listen for the results event and add every result to the map
        searchControl.on(\"results\", function(data) {
            results.clearLayers();
            for (var i = data.results.length - 1; i >= 0; i--) {
                results.addLayer(L.marker(data.results[i].latlng, { icon: IconSearch }));
            }
        });
        
        // ------ END 

";
}
if( $baseLayers == 1 ) {
    $map .= "L.control.layers(baseLayers).addTo(map);";
}
    $map .= "
        var markers = L.markerClusterGroup();
	";
if( $type == 'location' ) {
    $map .= "

		for (var i = 0; i < addressPoints.length; i++) {
			var a = addressPoints[i];
			var title = a[2];
            var myIcon = L.icon({
                iconUrl: '' + a[6] + '',
                iconSize:     [".esc_attr($icon_width).", ".esc_attr($icon_height)."],
                iconAnchor:   [".(esc_attr($icon_width)/2).", ".esc_attr($icon_height)."],
                popupAnchor:  [-3, -".(esc_attr($icon_height))."],
            });
            // create popup contents
            var customPopup = '' + a[4] + '<div class=\"em-osm-content\"><a href=\"' + a[3] + '\" target=\"_blank\"><h3>' + title + '</h3></a><p>' + a[5] + '</p></div><div class=\"clear\"></div><div class=\"em-osm-readmore\"><a href=\"' + a[3] + '\" target=\"_blank\">".esc_attr($readmore)."</a></div><br />';
            
            // specify popup options 
            var customOptions = {'maxWidth': '500','className' : 'customevent'}

			var marker = L.marker(new L.LatLng(a[0], a[1]), { title: title, icon: myIcon });
			marker.bindPopup(customPopup,customOptions);
			markers.addLayer(marker);
		}
";
    if( $legend_location == 1 ) {
    $map .= "
        // create legend : https://tomik23.github.io/leaflet-examples/#62.count-markers

        const legend = L.control({ position: 'bottomleft' });

        legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'description');
        L.DomEvent.disableClickPropagation(div);

        const allMarkers = L.DomUtil.create('div', 'all-markers');
        allMarkers.insertAdjacentHTML(
            'beforeend',
            '<div style=\"background-color:#ffffff;padding:0.8em;\">".__('All locations on map:', EMOSM_TXT_DOMAIN)." <strong>' + i +'</strong></div>'
        );

        div.appendChild(allMarkers);
        return div;
        };

        legend.addTo(map);
    ";
    }

} else if( $type == 'events') { 

    $map .= "
    for (var i = 0; i < addressPoints.length; i++) {
        var a = addressPoints[i];
        var title = a[2];
        var urlIcon = a[6]
        var catIcon = L.icon({
            iconUrl: urlIcon,
            iconSize:     [".esc_html($icon_width).", ".esc_html($icon_height)."],
            iconAnchor:   [".(esc_html($icon_width/2)).", ".esc_html($icon_height)."],
            popupAnchor:  [-3, -".(esc_html($icon_height))."],
        });
        // create popup contents
        var customPopup = '' + a[4] + '<div class=\"em-osm-event-content\"><a href=\"' + a[3] + '\" target=\"_blank\"><h3>' + title + '</h3></a><p>' + a[5] + '</p></div><div class=\"clear\"></div><div class=\"em-osm-event-readmore\"><a href=\"' + a[3] + '\" target=\"_blank\">".esc_html($readmore)."</a></div><br />';
        
        // specify popup options 
        var customOptions = {'maxWidth': '500','className' : 'customevent'}

        var marker = L.marker(new L.LatLng(a[0], a[1]), { title: title, icon: catIcon });
        marker.bindPopup(customPopup,customOptions);
        markers.addLayer(marker);
    }";
    if( $legend_events == 1 ) {
    $map .= "
    // create legend : https://tomik23.github.io/leaflet-examples/#62.count-markers

    const legend = L.control({ position: 'bottomleft' });

    legend.onAdd = function () {
    const div = L.DomUtil.create('div', 'description');
    L.DomEvent.disableClickPropagation(div);

    const allMarkers = L.DomUtil.create('div', 'all-markers');
    allMarkers.insertAdjacentHTML(
        'beforeend',
        '<div style=\"background-color:#ffffff;padding:0.8em;\">".__('All events on map:', EMOSM_TXT_DOMAIN)." <strong>' + i +'</strong></div>'
    );

    div.appendChild(allMarkers);
    return div;
    };

    legend.addTo(map);
";
    }
}
    $map .= "
		map.addLayer(markers);
";
    if( $home_button == 1 ) {
        if ( wp_is_mobile() ) : 
            
        else :
        $map .= '
        /// ------ HOME BUTTON
        const lat = '.$latitude.';
        const lng = '.$longitude.';
        const htmlTemplate =
        "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 32 32\"><path d=\"M32 18.451L16 6.031 0 18.451v-5.064L16 .967l16 12.42zM28 18v12h-8v-8h-8v8H4V18l12-9z\" /></svg>";

        // create custom button
        const customControl = L.Control.extend({
        // button position
        options: {
            position: "topleft",
        },

        // method
        onAdd: function (map) {
            //console.log(map.getCenter());
            // create button
            const btn = L.DomUtil.create("button");
            btn.title = "'.__('Back to Home', EMOSM_TXT_DOMAIN).'";
            btn.innerHTML = htmlTemplate;
            btn.className += "leaflet-bar back-to-home";
            btn.setAttribute(
            "style",
            "margin-top: 46px; left: 0; display: none; cursor: pointer; justify-content: center;"
            );

            return btn;
        },
        });

        // adding new button to map controll
        map.addControl(new customControl());

        const button = document.querySelector(".back-to-home");

        // on drag end
        map.on("dragend", getCenterOfMap);

        // on zoom end
        map.on("zoomend", getCenterOfMap);

        function getCenterOfMap() {
        const { lat, lng } = map.getCenter();
        const latDZ = lat.toFixed(5) * 1;
        const lngDZ = lng.toFixed(5) * 1;

        arrayCheckAndClick([latDZ, lngDZ]);
        }

        // compare two arrays, if arrays diffrent show button home-back
        function arrayCheckAndClick(array) {
        const IfTheDefaultLocationIsDifferent =
            [lat, lng].sort().join(",") !== array.sort().join(",");

        button.style.display = IfTheDefaultLocationIsDifferent ? "flex" : "none";

        // clicking on home-back set default view and zoom
        button.addEventListener("click", function () {
            // more fancy back to previous place
            map.flyTo([lat, lng], zoom);
            button.style.display = "none";
        });
        }
        /// ------ 
        ';
        endif;
    }
    if( $mini_map == 1 ) {
        if ( wp_is_mobile() ) : 
            $toggleDisplay = "false";
        else :
            $toggleDisplay = "true";
        endif;
        $map .= "
        const attribution =
        '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors';
        const osm2 = new L.TileLayer('".get_mapTile($paramMMode['tile'])."', { minZoom: 0, maxZoom: 13, attribution, id: 'mapbox/streets-v11' });
        var miniMap = new L.Control.MiniMap(osm2, { toggleDisplay: ".esc_html($toggleDisplay)." }).addTo(map);
        ";
    }

$map .= "

	</script>";

    return $map;
}
add_shortcode( 'em_osmap', 'em_openstreetmap_map' );


function em_openstreetmap_map_categories( $atts ) {

    global $EM_Location;
    global $post;

    // pour le formatage
    $order   = array("\r\n", "\n", "\r", "<p>");
    $replace = '<br />';

    // Récupère les paramètres sauvegardés
    if(get_option('em_openstreetmap_setting')) { extract(get_option('em_openstreetmap_setting')); }
    $paramMMode = get_option('em_openstreetmap_setting');

    if( isset($paramMMode['latitude']) && $paramMMode['latitude'] != '') { $latitude = esc_attr($paramMMode['latitude']); } else {  $latitude = 47.4; }
    if( isset($paramMMode['longitude']) && $paramMMode['longitude'] != '') { $longitude = esc_attr($paramMMode['longitude']); } else { $longitude = 1.6; }
    if( isset($paramMMode['zoom']) && $paramMMode['zoom'] != '') { $zoom = esc_attr($paramMMode['zoom']); } else { $zoom = 5.5; }
    if( isset($paramMMode['tile']) && $paramMMode['tile'] != '') { $tile = esc_html($paramMMode['tile']); } else { $tile = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }
    if( isset($paramMMode['map_icon']) && $paramMMode['map_icon'] != '') { $icon = esc_html($paramMMode['map_icon']); } else { $icon = 'default'; }

    if( isset($paramMMode["map_icon_size_width"]) && $paramMMode['map_icon_size_width'] != '' ) { $icon_width = 33; }
    if( isset($paramMMode["map_icon_size_height"]) && $paramMMode['map_icon_size_height'] != '' ) { $icon_height = 44; }

    // Attributes
    extract( shortcode_atts(
        array(
            'type' => 'location',
            'name' => '',
            'thumbnails' => 1,
            'state' => 0,
            'region' => 0,
            'country' => 0,
            'baseLayers' => 1,
            'search' => 1,
            'icon_url' => ''.esc_url(EMOSM_URL.'images/markers/'.$icon.'.png').'',
            'icon_width' => 33,
            'icon_height' => 44,
            'readmore' => __( 'Read more', EMOSM_TXT_DOMAIN),
            'legend_location' => 1,
            'legend_events' => 1,
            'cat' => '',
            'limit' => 0,
            'height' => 450,
            'map_latitude' => '',
            'map_longitude' => '',
            'clickZoom' => 1,
            'clickValZoom' => 10,
            'cat' => '',
            'mini_map' => 1,
            'home_button' => 1
        ), $atts )
    );

    $upload_dir = wp_upload_dir();
    $createDirectory = EM_Openstreetmap_Class::em_openstreetmap_folder_uploads('categories');
    $genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate('categories');
    // Nom du fichier XML
    //$pathXml = $createDirectory.'/xml-export-categories.js';

    $nameMap = '';
    if( isset($name) && $name!='') {
        $nameMap = '_'.esc_html($name);
        $map = "<style>#map".$nameMap." {height:".esc_attr($height)."px;}</style>";
    }

    $map = "<div id=\"map".$nameMap."\"></div>
    <ul class=\"catlayers\"></ul>";

    $map .= "<script src=\"".esc_js(str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $genereFile))."\"></script>
    ";
    $map .= "<script>
    // OPTIONS FOR MAP
    ";
    if( $baseLayers == 1 ) {
        $map .= "L.control.layers(baseLayers).addTo(map);";
    }
    if( $search == 1 ) {
    $map .= '
    /// ------ GEOCODER
    var IconSearch = L.icon({
        iconUrl: "'.esc_url(EMOSM_URL.'images/iconsearch.png').'",
        iconSize:     [32, 48],
        iconAnchor:   [16, 48],
        popupAnchor:  [-3, -48],
    });

    var optionsSearch = {
        placeholder: "'.__('Search for places or addresses', EMOSM_TXT_DOMAIN).'",
        //position: "topright"
    }
            
    // create the geocoding control and add it to the map
    var searchControl = L.esri.Geocoding.geosearch(optionsSearch).addTo(map);

    // create an empty layer group to store the results and add it to the map
    var results = L.layerGroup().addTo(map);

    // listen for the results event and add every result to the map
    searchControl.on("results", function(data) {
        results.clearLayers();
        for (var i = data.results.length - 1; i >= 0; i--) {
            results.addLayer(L.marker(data.results[i].latlng, { icon: IconSearch }));
        }
    });

    // ------ END 
    ';
    }
    if( $home_button == 1 ) {

        if ( wp_is_mobile() ) : 
            
        else :
            
        $map .= '
    /// ------ HOME BUTTON
    const htmlTemplate =
    "<svg xmlns=\"https://www.w3.org/2000/svg\" viewBox=\"0 0 32 32\"><path d=\"M32 18.451L16 6.031 0 18.451v-5.064L16 .967l16 12.42zM28 18v12h-8v-8h-8v8H4V18l12-9z\" /></svg>";

    // create custom button
    const customControl = L.Control.extend({
    // button position
    options: {
        position: "topleft",
    },

    // method
    onAdd: function (map) {
        //console.log(map.getCenter());
        // create button
        const btn = L.DomUtil.create("button");
        btn.title = "'.__('Back to Home', EMOSM_TXT_DOMAIN).'";
        btn.innerHTML = htmlTemplate;
        btn.className += "leaflet-bar back-to-home";
        btn.setAttribute(
        "style",
        "margin-top: 46px; left: 0; display: none; cursor: pointer; justify-content: center;"
        );

        return btn;
    },
    });

    // adding new button to map controll
    map.addControl(new customControl());

    const button = document.querySelector(".back-to-home");

    // on drag end
    map.on("dragend", getCenterOfMap);

    // on zoom end
    map.on("zoomend", getCenterOfMap);

    function getCenterOfMap() {
    const { lat, lng } = map.getCenter();
    const latDZ = lat.toFixed(5) * 1;
    const lngDZ = lng.toFixed(5) * 1;

    arrayCheckAndClick([latDZ, lngDZ]);
    }

    // compare two arrays, if arrays diffrent show button home-back
    function arrayCheckAndClick(array) {
    const IfTheDefaultLocationIsDifferent =
        [lat, lng].sort().join(",") !== array.sort().join(",");

    button.style.display = IfTheDefaultLocationIsDifferent ? "flex" : "none";

    // clicking on home-back set default view and zoom
    button.addEventListener("click", function () {
        // more fancy back to previous place
        map.flyTo([lat, lng], zoom);
        button.style.display = "none";
    });
    }
    /// ------ 
        ';
        endif;
    }

    if( $mini_map == 1 ) {
        if ( wp_is_mobile() ) : 
            $toggleDisplay = "false";
        else :
            $toggleDisplay = "true";
        endif;
        $map .= "
    const attribution =
    '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors';
    const osm2 = new L.TileLayer('".get_mapTile(1)."', { minZoom: 0, maxZoom: 13, attribution, id: 'mapbox/streets-v11' });
    var miniMap = new L.Control.MiniMap(osm2, { toggleDisplay: ".esc_html($toggleDisplay)." }).addTo(map);
    ";
    }
    $map .= "</script>";


    return $map;
}
add_shortcode( 'em_osmap_categories', 'em_openstreetmap_map_categories' );

function get_mapTile($id) {
    
    if( empty($id) || $id =='' || !is_numeric($id) ) { return 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }

    // Récupère les paramètres sauvegardés
    if(get_option('em_openstreetmap_setting')) { extract(get_option('em_openstreetmap_setting')); }
    $paramMMode = get_option('em_openstreetmap_setting');

    if(isset($paramMMode['custom_tile']) && $paramMMode['custom_tile']!='') {
        $customUrl = 'https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token='.esc_html($paramMMode['custom_tile']); 
    } else { 
        $customUrl = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png';
    }

    $tabTile = array(
        1 => 'https://{s}.tile.osm.org/{z}/{x}/{y}.png',
        2 => 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
        3 => 'https://a.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
        4 => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        5 => 'https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png',
        6 => $customUrl
    );
    return $tabTile[$id];
}