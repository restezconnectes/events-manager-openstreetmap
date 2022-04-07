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

        $latitude = get_post_meta($post->ID, '_location_latitude', true);
        $longitude = get_post_meta($post->ID, '_location_longitude', true);

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


            $mapHeight = get_post_meta($post->ID, '_location_osm_map_height', true);
            if( isset($mapHeight) && $mapHeight!='' ) { $height = esc_attr($mapHeight); } else { $height = '250'; }

            $mapLayer = get_post_meta($post->ID, '_location_osm_map_layer', true);
            if( isset($mapLayer) && $mapLayer!='' ) { $layer = esc_html($mapLayer); } else { $layer = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }

            $mapZoom = get_post_meta($post->ID, '_location_osm_map_zoom', true);
            if( isset($mapZoom) && $mapZoom!='' ) { $zoom = esc_html($mapZoom); } else { $zoom = 13; }

            $mapIcon = get_post_meta($post->ID, '_location_osm_map_icon', true);
            if( (isset($mapIcon) && $mapIcon=="") || empty($mapIcon) ) { $mapIcon = 'default'; }
            $urlIcon = EMOSM_PLUGIN_URL.'images/markers/'.$mapIcon.'.png';
            
            // On va chercher l'icon de la categorie d'evenement
            if( isset($mapIcon) && $mapIcon=="none" ) { 
                $EM_Categories = $EM_Event->get_categories();
                if( $EM_Categories ) {
                    foreach( $EM_Categories AS $EM_Category){
                        $iconId = get_term_meta ( $EM_Category->term_id, 'em-categories-icon-id', true );
                        if( $iconId !='' && is_numeric($iconId) ) {
                            $urlIcon = wp_get_attachment_url($iconId);
                        } else {
                            $urlIcon = EMOSM_PLUGIN_URL.'images/markers/default.png';
                            
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
            if ( $urlIcon == '' ) { $urlIcon = EMOSM_PLUGIN_URL.'images/markers/default.png'; }

            $EM_Location = em_get_location($EM_Event->location_id);

            $replace = '<!-- '.$urlIcon.'-->
            <div id="event-map" style="height: '.$height.'px!important;"></div>';
            $replace .= "<script>
                const map = L.map('event-map').setView([".$latitude.", ".$longitude."], ".$zoom.");
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

    if( isset($paramMMode['latitude']) && $paramMMode['latitude'] != '') { $latitude = $paramMMode['latitude']; } else {  $latitude = 47.4; }
    if( isset($paramMMode['longitude']) && $paramMMode['longitude'] != '') { $longitude = $paramMMode['longitude']; } else { $longitude = 1.6; }
    if( isset($paramMMode['zoom']) && $paramMMode['zoom'] != '') { $zoom = $paramMMode['zoom']; } else { $zoom = 5.5; }
    if( isset($paramMMode['tile']) && $paramMMode['tile'] != '') { $tile = $paramMMode['tile']; } else { $tile = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }
    if( isset($paramMMode['map_icon']) && $paramMMode['map_icon'] != '') { $icon = $paramMMode['map_icon']; } else { $icon = 'default'; }

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
            'icon_url' => plugins_url()."/events-manager-openstreetmap/images/markers/".$icon.".png",
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
			}),";

        if( isset($map_latitude) && $map_latitude!='' && $map_longitude!='' ) {
    $map .= "latlng = L.latLng(".esc_html($map_latitude).", ".esc_html($map_longitude)."); ";        
        } else {
    $map .= "latlng = L.latLng(".esc_html($latitude).", ".esc_html($longitude).");";
        }
    $map .= "
        var options = {
            maxZoom: 18,
            center: latlng,
            zoom: ".intval($zoom).",
            layers: [tiles],
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
            iconUrl: '".plugins_url()."/events-manager-openstreetmap/images/iconsearch.png',
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
                iconSize:     [".esc_html($icon_width).", ".esc_html($icon_height)."],
                iconAnchor:   [".(esc_html($icon_width)/2).", ".esc_html($icon_height)."],
                popupAnchor:  [-3, -".(esc_html($icon_height))."],
            });
            // create popup contents
            var customPopup = '' + a[4] + '<div class=\"em-osm-content\"><a href=\"' + a[3] + '\" target=\"_blank\"><h3>' + title + '</h3></a><p>' + a[5] + '</p></div><div class=\"clear\"></div><div class=\"em-osm-readmore\"><a href=\"' + a[3] + '\" target=\"_blank\">".esc_html($readmore)."</a></div><br />';
            
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
    }
    if( $mini_map == 1 ) {
        $map .= "
        const attribution =
        '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors';
        const osm2 = new L.TileLayer('".$tile."', { minZoom: 0, maxZoom: 13, attribution, id: 'mapbox/streets-v11' });
        var miniMap = new L.Control.MiniMap(osm2, { toggleDisplay: true }).addTo(map);
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

    if( isset($paramMMode['latitude']) && $paramMMode['latitude'] != '') { $latitude = $paramMMode['latitude']; } else {  $latitude = 47.4; }
    if( isset($paramMMode['longitude']) && $paramMMode['longitude'] != '') { $longitude = $paramMMode['longitude']; } else { $longitude = 1.6; }
    if( isset($paramMMode['zoom']) && $paramMMode['zoom'] != '') { $zoom = $paramMMode['zoom']; } else { $zoom = 5.5; }
    if( isset($paramMMode['tile']) && $paramMMode['tile'] != '') { $tile = $paramMMode['tile']; } else { $tile = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }
    if( isset($paramMMode['map_icon']) && $paramMMode['map_icon'] != '') { $icon = $paramMMode['map_icon']; } else { $icon = 'default'; }

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
            'icon_url' => plugins_url()."/events-manager-openstreetmap/images/markers/".$icon.".png",
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
        $map = "<style>#map".$nameMap." {height:".esc_html($height)."px;}</style>";
    }

    $map = "<div id=\"map".$nameMap."\"></div>
    <ul class=\"catlayers\"></ul>";

    $map .= "<script src=\"".str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $genereFile)."\"></script>";


    return $map;
}
add_shortcode( 'em_osmap_categories', 'em_openstreetmap_map_categories' );

function em_openstreetmap_map_cat( $atts ) {

    global $EM_Location;
    global $post;

    // pour le formatage
    $order   = array("\r\n", "\n", "\r", "<p>");
    $replace = '<br />';

    // Récupère les paramètres sauvegardés
    if(get_option('em_openstreetmap_setting')) { extract(get_option('em_openstreetmap_setting')); }
    $paramMMode = get_option('em_openstreetmap_setting');

    if( isset($paramMMode['latitude']) && $paramMMode['latitude'] != '') { $latitude = $paramMMode['latitude']; } else {  $latitude = 47.4; }
    if( isset($paramMMode['longitude']) && $paramMMode['longitude'] != '') { $longitude = $paramMMode['longitude']; } else { $longitude = 1.6; }
    if( isset($paramMMode['zoom']) && $paramMMode['zoom'] != '') { $zoom = $paramMMode['zoom']; } else { $zoom = 5.5; }
    if( isset($paramMMode['tile']) && $paramMMode['tile'] != '') { $tile = $paramMMode['tile']; } else { $tile = 'https://{s}.tile.osm.org/{z}/{x}/{y}.png'; }
    if( isset($paramMMode['map_icon']) && $paramMMode['map_icon'] != '') { $icon = $paramMMode['map_icon']; } else { $icon = 'default'; }

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
            'icon_url' => plugins_url()."/events-manager-openstreetmap/images/markers/".$icon.".png",
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
        ), $atts )
    );

    $upload_dir = wp_upload_dir();
    //$genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate($type, $cat, $limit);
    $file_eventcontents = '';
    $listEvents = EM_Events::get( array('limit'=>$limit, 'category' => $cat, 'scope' => 'future', 'owner'=>false) );
    $events_count = EM_Events::count();
    $xmlEventsCatPoint = array();
    $catName = array();
    if( $events_count >= 1 ) {

        foreach ( $listEvents as $event ) {

            // On va cherche les coordonnées du lieu
            $EM_Location = em_get_location($event->location_id);
            if( isset($EM_Location->location_latitude) && $EM_Location->location_latitude!='' ) {

                $idThumbnail = get_post_thumbnail_id( $event->post_id );
                $thumbnail = '';
                if ( has_post_thumbnail($event->post_id) ) {
                $thumbnail = '<div class=\"em-osm-catevent-thumbnail\"><a href=\"'.get_the_permalink($event->post_id).'\">'.addslashes(get_the_post_thumbnail( $event->post_id, array(100, 100))).'</a></div>';
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

                $mapIcon = get_post_meta($event->post_id, '_location_osm_map_icon', true);
                if( isset($mapIcon) && $mapIcon != '') { $icon = $mapIcon; } else { $icon = 'default'; }
                
                $urlIconEvent = EMOSM_PLUGIN_URL.'images/markers/'.$icon.'.png';
                
                // On va cherche les coordonnées du lieu
                $EM_Location = em_get_location($event->location_id);
                // On va chercher l'icon de la categorie d'evenement
                $EM_Categories = $event->get_categories();
                if( $EM_Categories ) {              
                    foreach( $EM_Categories AS $EM_Category){
                        $icon_id = get_term_meta ( $EM_Category->term_id, 'em-categories-icon-id', true );
                        if( (isset($icon) && $icon == 'none') && (isset($icon_id) && is_numeric($icon_id) && $icon_id !='')){
                            $urlIconEvent = wp_get_attachment_url($icon_id);
                        }
                        $xmlEventsCatPoint[$EM_Category->term_id][] = '["'.$EM_Location->location_latitude.'", "'.$EM_Location->location_longitude.'", "'.esc_html($titleEvent).'", "'.get_the_permalink($event->post_id).'", "'.$thumbnail.'", "'.addslashes($textContentEvent).'", "'.$urlIconEvent.'", "'.$EM_Category->term_id.'"],
                        ';
                        $catName[$EM_Category->term_id] = $EM_Category->name;
                    }
                }
            }
        }
        $pointByCat = '';
        $markerCat = '';
        $overlayMaps = '';
        foreach( $xmlEventsCatPoint as $idCat => $catpoint){
            $pointByCat .= 'const p'.$idCat.' = new L.FeatureGroup();
const cat'.$idCat.' = [
';
            for($i = 0; $i < count($catpoint); $i++) {
$pointByCat .= $catpoint[$i];
            }
            $pointByCat .= '];

';
$markerCat .= "
// adding markers to the layer cat0
for (let i = 0; i < cat".$idCat.".length; i++) {

    const urlIcon = cat".$idCat."[i][6]
    const title = cat".$idCat."[i][2];

    const catIcon = L.icon({
        iconUrl: urlIcon,
        iconSize:     [".esc_html($icon_width).", ".esc_html($icon_height)."],
        iconAnchor:   [".(esc_html($icon_width/2)).", ".esc_html($icon_height)."],
        popupAnchor:  [-3, -".(esc_html($icon_height))."],
    });

    // create popup contents
    const customPopup = '' + cat".$idCat."[i][4] + '<div class=\"em-osm-catevent-content\"><a href=\"' + cat".$idCat."[i][3] + '\" target=\"_blank\"><h3>' + title + '</h3></a><p>' + cat".$idCat."[i][5] + '</p></div><div class=\"clear\"></div><div class=\"em-osm-catevent-readmore\"><a href=\"' + cat".$idCat."[i][3] + '\" target=\"_blank\">".esc_html($readmore)."</a></div><br />';

    // specify popup options 
    const customOptions = {'maxWidth': '500','className' : 'customevent'}
    marker = L.marker(new L.LatLng(cat".$idCat."[i][0], cat".$idCat."[i][1]), { title: title, icon: catIcon });
    marker.bindPopup(customPopup,customOptions);";
    if( $clickZoom == 1 ) {
        $markerCat .= "marker.on('click', clickZoom);";
    }
    //marker = L.marker([cat".$idCat."[i][0], cat".$idCat."[i][1]], { title: title, icon: catIcon }).bindPopup(customPopup,customOptions);
    $markerCat .= "p".$idCat.".addLayer(marker);

}
";

$overlayMaps .= "'".$catName[$idCat]."': p".$idCat.",
";
        }
    }

    $nameMap = '';
    if( isset($name) && $name!='') {
        $nameMap = '_'.esc_html($name);
        $map = "<style>#map".$nameMap." {height:".esc_html($height)."px;}</style>";
    }
    $map = "<div id=\"map".$nameMap."\" class=\"leaflet-container leaflet-touch leaflet-fade-anim leaflet-grab leaflet-touch-drag leaflet-touch-zoom\">
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
			});
        ";
        if( isset($map_latitude) && $map_latitude!='' && $map_longitude!='' ) {
        $map .= "latlng = L.latLng(".esc_html($map_latitude).", ".esc_html($map_longitude)."); ";        
                } else {
        $map .= "latlng = L.latLng(".esc_html($latitude).", ".esc_html($longitude).");";
                }
        $map .= "
        var options = {
            maxZoom: 18,
            center: latlng,
            zoom: ".intval($zoom).",
            layers: [tiles],
        }
        
        var baseLayers = {
            'Custom OpenStreetMap': tiles,
            'OpenStreetMap France': esriMap,
            'OSM Mapnik': osmMap,
            'OpenStreetMap HOT': landMap,        
            'Satellite':EsriWorldImagery,
            'Cycle OSM':CyclOSM
        };

        // config map
        let config = {
        minZoom: 5.5,
        maxZoom: 18,
        };
        // magnification with which the map will start
        const zoom = 6;
        // coordinates
        const lat = ".$paramMMode['latitude'].";
        const lng = ".$paramMMode['longitude'].";

".$pointByCat."

        // calling map
        //const map = L.map('map".$nameMap."', options).setView([lat, lng], zoom);
        const map = L.map('map".$nameMap."', options);
        
        ";
if( $search == 1) {
    $map .= "
        /// ------ GEOCODER
        var IconSearch = L.icon({
            iconUrl: '".plugins_url()."/events-manager-openstreetmap/images/iconsearch.png',
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
        // Used to load and display tile layers on the map
        // Most tile servers require attribution, which you can set under `Layer`
        L.tileLayer('".$tile."', {
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1,
            attribution:
            '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors',
        }).addTo(map);
        
        // Extended `LayerGroup` that makes it easy
        // to do the same for all layers of its members

        const allMarkers = new L.FeatureGroup();

        // set center map
        function clickZoom(e) {
            map.setView(e.target.getLatLng(), ".$clickValZoom.");
            // pantTo version
            // map.panTo(e.target.getLatLng());
        }


        ".$markerCat."

        // object with layers
        const overlayMaps = {
            ".$overlayMaps."
        };
        

        // centering a group of markers
        map.on('layeradd layerremove', function () {
        // Create new empty bounds
        let bounds = new L.LatLngBounds();
        // Iterate the map's layers
        map.eachLayer(function (layer) {
            // Check if layer is a featuregroup
            if (layer instanceof L.FeatureGroup) {
            // Extend bounds with group's bounds
            bounds.extend(layer.getBounds());
            }
        });

        // Check if bounds are valid (could be empty)
        if (bounds.isValid()) {
            // Valid, fit bounds
            map.flyToBounds(bounds);
        } else {
            // Invalid, fit world
            //map.fitWorld();
        }
        });

        L.Control.CustomButtons = L.Control.Layers.extend({
        onAdd: function () {
            this._initLayout();
            this._addMarker();
            this._removeMarker();
            this._update();
            return this._container;
        },
        _addMarker: function () {
            this.createButton('add', 'add-button');
        },
        _removeMarker: function () {
            this.createRemoveButton('remove', 'remove-button');
        },
        
        createButton: function (type, className) {
            const elements = this._container.getElementsByClassName(
            'leaflet-control-layers-list'
            );
            const button = L.DomUtil.create(
            'button',
            'btn-markers add-button',
            elements[0]
            );
            button.textContent = '".__( 'All', EMOSM_TXT_DOMAIN)."';

            L.DomEvent.on(button, 'click', function (e) {
            const checkbox = document.querySelectorAll(
                '.leaflet-control-layers-overlays input[type=checkbox]'
            );

            // Remove/add all layer from map when click on button
            [].slice.call(checkbox).map((el) => {
                el.checked = type === 'add' ? false : true;
                el.click();
            });
            });
        },
        createRemoveButton: function (type, className) {
            const elements = this._container.getElementsByClassName(
            'leaflet-control-layers-list'
            );
            const button = L.DomUtil.create(
            'button',
            'btn-markers remove-button',
            elements[0]
            );
            button.textContent = '".__( 'Remove', EMOSM_TXT_DOMAIN)."';

            L.DomEvent.on(button, 'click', function (e) {
            const checkbox = document.querySelectorAll(
                '.leaflet-control-layers-overlays input[type=checkbox]'
            );

            // Remove/add all layer from map when click on button
            [].slice.call(checkbox).map((el) => {
                el.checked = type === 'add' ? false : true;
                el.click();
            });
            });
        },
        });

        new L.Control.CustomButtons(null, overlayMaps, { collapsed: false }).addTo(map);
        

	</script>";

    return $map;
}
//add_shortcode( 'em_osmap_cat', 'em_openstreetmap_map_cat' );