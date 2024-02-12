<?php

defined( 'ABSPATH' ) or die( 'Not allowed' );

global $_wp_admin_css_colors;

/* FONCTION UPDATE */
function emosm_update_settings($tabSettings, $nameOption = '', $type = 1) {

    if(empty($nameOption) || $nameOption =='') { return false; }

    if(isset($tabSettings) && is_array($tabSettings)) {
        $newTabSettings = array();
        foreach($tabSettings as $nameSettings => $valueSettings) {
            $newTabSettings[$nameSettings] = sanitize_textarea_field($valueSettings);
        }
        update_option($nameOption, $newTabSettings);

        return true;
    } else {
        return false;
    }
    
}

/* Update des paramètres */
if(isset($_POST['action']) && $_POST['action'] == 'update_settings' && wp_verify_nonce($_POST['security-settings'], 'valid-settings')) {

    if(isset($_POST["em_page_idmap"]) && is_numeric($_POST["em_page_idmap"])) {
        update_option('em_openstreetmap_location_page', sanitize_text_field($_POST["em_page_idmap"]));
    }
    if(isset($_POST["em_page_events_idmap"]) && is_numeric($_POST["em_page_events_idmap"])) {
        update_option('em_openstreetmap_events_page', sanitize_text_field($_POST["em_page_events_idmap"]));
    }
    if(isset($_POST["em_page_cat_idmap"]) && is_numeric($_POST["em_page_cat_idmap"])) {
        update_option('em_openstreetmap_categories_page', sanitize_text_field($_POST["em_page_cat_idmap"]));
    }
   
    $updateSetting = emosm_update_settings($_POST["em_openstreetmap_setting"], 'em_openstreetmap_setting');
    if($updateSetting == true) { echo '<div id="message" class="updated fade"><p><strong>'.__('Settings are saved!', EMOSM_TXT_DOMAIN).'</strong></p></div>'; }
    // END UPDATE SETTINGS

    $genereEventsFile = EM_Openstreetmap_Class::em_openstreetmap_generate('events', '', '', 1);
    $genereLocationFile = EM_Openstreetmap_Class::em_openstreetmap_generate('location', '', '', 1);
    $genereCategorieFile = EM_Openstreetmap_Class::em_openstreetmap_generate('categories', '', '', 1);

}

// Récupère les paramètres sauvegardés
if(get_option('em_openstreetmap_setting')) { extract(get_option('em_openstreetmap_setting')); }
$paramMMode = get_option('em_openstreetmap_setting');

$admin_color = get_user_option('admin_color', get_current_user_id());
$colors      = $_wp_admin_css_colors[$admin_color]->colors;


?>
<style type="text/css">
.switch-field input:checked + label { background-color: <?php echo esc_html($colors[2]); ?>; }
.switch-field input:checked + label:last-of-type {
    background-color: <?php echo esc_html($colors[0]); ?>!important;
    color:#e4e4e4!important;
}
.switch-field-mini input:checked + label { background-color: <?php echo esc_html($colors[2]); ?>; }
.switch-field-mini input:checked + label:last-of-type {background-color: <?php echo esc_html($colors[0]); ?>!important;color:#e4e4e4!important;}

.inputmap {border: 1px solid #ececec!important;padding: 0 8px 0 8px!important;line-height: 2!important;min-height: 30px!important;text-align: left!important; }
.switch-field{font-family:"Lucida Grande",Tahoma,Verdana,sans-serif;padding-top:5px;padding-bottom:10px;overflow:hidden;width:180px}
.switch-title{margin-bottom:6px}
.switch-field input{position:absolute!important;clip:rect(0,0,0,0);height:1px;width:1px;border:0;overflow:hidden}
.switch-field label{float:left;display:inline-block;width:60px;background-color:#e4e4e4;color:#333;font-size:14px;font-weight:400;text-align:center;text-shadow:none;padding:6px 14px;border:1px solid rgba(0,0,0,0.2);-webkit-box-shadow:inset 0 1px 3px rgba(0,0,0,0.3),0 1px rgba(255,255,255,0.1);box-shadow:inset 0 1px 3px rgba(0,0,0,0.3),0 1px rgba(255,255,255,0.1);-webkit-transition:all .1s ease-in-out;-moz-transition:all .1s ease-in-out;-ms-transition:all .1s ease-in-out;-o-transition:all .1s ease-in-out;transition:all .1s ease-in-out}
.switch-field label:hover{cursor:pointer}
.switch-field input:checked + label{-webkit-box-shadow:none;box-shadow:none;color:#e4e4e4}
.switch-field-mini label{display:inline-block;text-align:center}
.switch-field-mini{font-family:"Lucida Grande",Tahoma,Verdana,sans-serif;overflow:hidden;margin-left:auto}
.switch-mini-title{margin-bottom:6px}
.switch-field-mini input{position:absolute!important;clip:rect(0,0,0,0);height:1px;width:1px;border:0;overflow:hidden}
.switch-field-mini label{float:left;width:40px;background-color:#e4e4e4;color:#333;font-size:12px;text-shadow:none;padding:0;border:1px solid rgba(0,0,0,.2);-webkit-box-shadow:inset 0 1px 3px rgba(0,0,0,.3),0 1px rgba(255,255,255,.1);box-shadow:inset 0 1px 3px rgba(0,0,0,.3),0 1px rgba(255,255,255,.1);-webkit-transition:all .1s ease-in-out;-moz-transition:all .1s ease-in-out;-ms-transition:all .1s ease-in-out;-o-transition:all .1s ease-in-out;transition:all .1s ease-in-out}
.switch-field-mini label:hover{cursor:pointer}
.switch-field-mini input:checked+label{-webkit-box-shadow:none;box-shadow:none;color:#e4e4e4}
.switch-field-mini label:first-of-type{border-radius:4px 0 0 4px}
.switch-field-mini label:last-of-type{border-radius:0 4px 4px 0}
.switch-field label:last-of-type{border-radius:0 4px 4px 0}

.CodeMirror {border: 1px solid #eee;height: auto;}
#emosm-general h2 sup {font-size: 14px;position: relative;font-weight: 400;background: #0085ba;color: #fff !important;padding: 2px 4px !important;border-radius: 3px;top: 5px;left: 3px;border: none !important;}
#fond-entete {background-image: url("<?php echo EMOSM_PLUGIN_URL; ?>/images/fond-entete.png");background-repeat: repeat-x;text-align: center;height: 124px;margin: 0!important;padding: 0!important;}
</style>
<div id="emosm-general" class="wrap">
    <h2><?php _e('EM OpenStreeMap', EMOSM_TXT_DOMAIN); ?> v.<?php echo esc_html(EMOSM_VERSION); ?></h2>
    <div>
        <form method="post" action="admin.php?page=em_openstreetmap_settings_page" name="valide_settings">
        <div style="margin-left: auto;width:20%;text-align:right;"><?php submit_button(); ?></div>
            <input type="hidden" name="action" value="update_settings" />
            <?php wp_nonce_field('valid-settings', 'security-settings'); ?>

            <table style="width: 80%;margin-left: auto;margin-right: auto;height: 94px;margin-top: 2em;">
                <tr>
                    <td style="width:95px;margin: 0!important;padding: 0!important;"><img src="<?php echo EMOSM_PLUGIN_URL; ?>/images/fond-g.png" width="95" height="124" /></td>
                    <td id="fond-entete">
                        <div style="width: 80%;margin-left: auto;margin-right: auto;margin-top: 2em;">
                            <div style="float:left;width:70%;"><img src="<?php echo EMOSM_PLUGIN_URL; ?>/images/fond-logo.png" width="142" height="34" /><h2 style="color:#ffffff;"><?php _e('Event Manager OpenStreeMap', EMOSM_TXT_DOMAIN); ?><sup><?php echo 'V.'.EMOSM_VERSION; ?></sup><h2></div>
                            <div style="float:left;width:30%;"><img src="<?php echo EMOSM_PLUGIN_URL; ?>/images/Openstreetmap_logo.png" width="95" height="95" /></div>
                        </div>
                    </td>
                    <td style="width:95px;margin: 0!important;padding: 0!important;"><img src="<?php echo EMOSM_PLUGIN_URL; ?>/images/fond-d.png" width="95" height="124" /></td>
                </tr>
            </table>

            <table style="width: 80%;margin-left: auto;margin-right: auto;">
                <tbody>                   
                    <tr style="background-color:#D8DCE1;">
                        <td style="padding:2em;width:125px;">
                        
                            <strong><?php _e( 'Select Location Map Page:', EMOSM_TXT_DOMAIN ); ?></strong>
                            <?php
                            if( get_option('em_openstreetmap_location_page' ) ) {
                                $idSelectPage = get_option('em_openstreetmap_location_page' );
                                $linkPage = ' (<a href="'.get_the_permalink($idSelectPage).'" target="_blank">'.__('See this page', EMOSM_TXT_DOMAIN).'</a>)';
                            } else {
                                $idSelectPage = 0;
                                $linkPage = '';
                            }
                            ?>
                            <p><?php _e('There must be the shortcode:', EMOSM_TXT_DOMAIN); ?> [em_osmap]<?php echo $linkPage; ?></p>
                            <?php
                            $argsLocation = array('name' => 'em_page_idmap', 'selected' => $idSelectPage, 'class' => 'inputmap','show_option_none' => __('Please select a page', EMOSM_TXT_DOMAIN)); 
                            wp_dropdown_pages($argsLocation);

                            ?><br /><br />
                            <strong><?php _e('Select Events Map Page:', EMOSM_TXT_DOMAIN); ?></strong>
                            <?php
                            if( get_option('em_openstreetmap_events_page' ) ) {
                                $idSelectPageEvents = get_option('em_openstreetmap_events_page' );
                                $linkPageEvents = ' (<a href="'.get_the_permalink($idSelectPageEvents).'" target="_blank">'.__('See this page', EMOSM_TXT_DOMAIN).'</a>)';
                            } else {
                                $idSelectPageEvents = 0;
                                $linkPageEvents = '';
                            }
                            ?>
                            <p><?php _e('There must be the shortcode:', EMOSM_TXT_DOMAIN); ?> [em_osmap type="events"]<?php echo $linkPageEvents; ?></p>
                            <?php
                            $argsEvents = array('name' => 'em_page_events_idmap', 'selected' => esc_html($idSelectPageEvents), 'class' => 'inputmap','show_option_none' => __('Please select a page', EMOSM_TXT_DOMAIN ) ); 
                            wp_dropdown_pages($argsEvents);

                            ?><br /><br />
                            <strong><?php _e('Select Cat Map Page:', EMOSM_TXT_DOMAIN); ?></strong>
                            <?php
                            if( get_option('em_openstreetmap_categories_page') ) {
                                $idSelectPageCat = get_option('em_openstreetmap_categories_page' );
                                $linkPageCat = ' (<a href="'.get_the_permalink($idSelectPageCat).'" target="_blank">'.__( 'See this page', EMOSM_TXT_DOMAIN ).'</a>)';
                            } else {
                                $idSelectPageCat = 0;
                                $linkPageCat = '';
                            }
                            ?>
                            <p><?php _e('There must be the shortcode:', EMOSM_TXT_DOMAIN); ?> [em_osmap_categories]<?php echo $linkPageCat; ?></p>
                            <?php
                            $argsCat = array('name' => 'em_page_cat_idmap', 'selected' => esc_html($idSelectPageCat), 'class' => 'inputmap','show_option_none' => __('Please select a page', EMOSM_TXT_DOMAIN ) ); 
                            wp_dropdown_pages($argsCat);

                            ?>
                            <br /><br /><hr /><br />
                            <strong><?php _e('Start coordinates:', EMOSM_TXT_DOMAIN); ?></strong><p></p>
                            <table>
                                    <tr>
                                        <td><?php _e('Latitude:', EMOSM_TXT_DOMAIN); ?></td>
                                        <td><?php _e('Longitude:', EMOSM_TXT_DOMAIN); ?></td>
                                        <td><?php _e('Zoom:', EMOSM_TXT_DOMAIN); ?></td>
                                    </tr>
                                    <tr>
                                        <td><input name="em_openstreetmap_setting[latitude]" size="10" class="inputmap" value="<?php if( isset($paramMMode['latitude']) && $paramMMode['latitude'] !='' ) { echo esc_html($paramMMode['latitude']); } else { echo '47.4'; } ?>" /></td>
                                        <td><input name="em_openstreetmap_setting[longitude]" size="10" class="inputmap" value="<?php if( isset($paramMMode['longitude']) && $paramMMode['longitude'] !='' ) { echo esc_html($paramMMode['longitude']); } else { echo '1.6'; } ?>" /></td>
                                        <td><input name="em_openstreetmap_setting[zoom]" size="10" class="inputmap" value="<?php if( isset($paramMMode['zoom']) && $paramMMode['zoom'] !='' ) { echo esc_html($paramMMode['zoom']); } else { echo '5.5'; } ?>" /></td>
                                    </tr>
                            </table><br /><hr /><br />
                            <strong><?php _e( 'Enter number of days before generate map expiration', EMOSM_TXT_DOMAIN ); ?></strong><p><?php _e( 'By default is 15 days.', EMOSM_TXT_DOMAIN ); ?></p>
                            <select name="em_openstreetmap_setting[expire]" class="inputmap">
                                <option value="1" <?php if(isset($paramMMode['expire']) && $paramMMode['expire'] == 1) { echo 'selected'; }?>>1 <?php _e('day', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                                <option value="3" <?php if(isset($paramMMode['expire']) && $paramMMode['expire'] == 3) { echo 'selected'; }?>>3 <?php _e('days', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                                <option value="5" <?php if(isset($paramMMode['expire']) && $paramMMode['expire'] == 5) { echo 'selected'; }?>>5 <?php _e('days', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                                <option value="7" <?php if(isset($paramMMode['expire']) && $paramMMode['expire'] == 7) { echo 'selected'; }?>>7 <?php _e('days', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                                <option value="10" <?php if(isset($paramMMode['expire']) && $paramMMode['expire'] == 10) { echo 'selected'; }?>>10 <?php _e('days', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                                <option value="15" <?php if((isset($paramMMode['expire']) && $paramMMode['expire'] == 15) || empty($paramMMode['expire']) ) { echo 'selected'; }?>>15 <?php _e( 'days', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                                <option value="20" <?php if(isset($paramMMode['expire']) && $paramMMode['expire'] == 20) { echo 'selected'; }?>>20 <?php _e('days', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                                <option value="25" <?php if(isset($paramMMode['expire']) && $paramMMode['expire'] == 25) { echo 'selected'; }?>>25 <?php _e('days', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                                <option value="30" <?php if(isset($paramMMode['expire']) && $paramMMode['expire'] == 30) { echo 'selected'; }?>>30 <?php _e('days', EMOSM_TXT_DOMAIN); ?>&nbsp;&nbsp;</option>
                            </select>
                            <br /><br /><hr /><br />
                            <strong><?php _e('Icon for Location Map:', EMOSM_TXT_DOMAIN); ?></strong><p><?php _e( 'By default is Default', EMOSM_TXT_DOMAIN ); ?></p>
                            <?php 
                            $listIcons = array(
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
                                "default" => __('Default', EMOSM_TXT_DOMAIN), 
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
                                "vacant-land" => __('Vacant Land', EMOSM_TXT_DOMAIN),
                            );
                            
                            ?>
                            <select name="em_openstreetmap_setting[map_icon]" class="inputmap">
                                <option value="default"><?php _e('Default Icon', EMOSM_TXT_DOMAIN); ?></option>
                                <?php foreach( $listIcons as $value => $name) { 
                                    $selected = '';
                                    if( isset($paramMMode['map_icon']) && $paramMMode['map_icon'] == $value) { $selected = 'selected'; }
                                    ?>
                                    <option value="<?php echo esc_html($value); ?>" <?php echo esc_html($selected); ?>><?php echo esc_html($name); ?></option>
                                <?php } ?>
                            </select><br /><br />
                            <strong><?php _e('Size icon for Location Map:', EMOSM_TXT_DOMAIN); ?></strong><p><?php _e('Width x Height', EMOSM_TXT_DOMAIN); ?></p>
                            <input name="em_openstreetmap_setting[map_icon_size_width]" size="5" class="inputmap" value="<?php if( isset($paramMMode['map_icon_size_width']) && $paramMMode['map_icon_size_width'] !='' ) { echo esc_html($paramMMode['map_icon_size_width']); } else { echo 34; } ?>" /> X 
                            <input name="em_openstreetmap_setting[map_icon_size_height]" size="5" class="inputmap" value="<?php if( isset($paramMMode['map_icon_size_height']) && $paramMMode['map_icon_size_height'] !='' ) { echo esc_html($paramMMode['map_icon_size_height']); } else { echo 44; } ?>" />
                            
                            <br /><br /><hr /><br />
                            <strong><?php _e('Tile for Location & Events Map:', EMOSM_TXT_DOMAIN); ?></strong><p></p>
                            <select name="em_openstreetmap_setting[tile]">
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
                                    if($valueTile == $paramMMode['tile']) { $selected = ' selected="selected"'; }
                                    echo '<option value="'.esc_html($valueTile).'" '.esc_html($selected).'>'.esc_html($nameTile).'</value>';
                                }
                                ?>
                                
                                <?php ?>
                            </select><br />
                            <!-- https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoicmVzdGV6Y29ubmVjdGVzIiwiYSI6ImNrcXVtcHd4aDA2MncydXJ5OHdrN242MG4ifQ.yxsWcR17v_epL7t-pcxltw -->
                            <input type="text" name="em_openstreetmap_setting[custom_tile]" placeholder="<?php _e('Enter here API MapBox access token', EMOSM_TXT_DOMAIN); ?>" style="width:100%;" value="<?php if(isset($paramMMode['custom_tile'])) { echo esc_html($paramMMode['custom_tile']); } ?>" />
                            <br /><br /><hr /><br />
                            <strong><?php _e('Custom CSS:', EMOSM_TXT_DOMAIN); ?></strong><p></p>
                            <TEXTAREA NAME="em_openstreetmap_setting[css]" id="emosmstyle" COLS=50 ROWS=2><?php if( isset($paramMMode['css']) && $paramMMode['css']!='' ) { echo esc_textarea(stripslashes($paramMMode['css'])); }  ?></TEXTAREA>
                            <br /><br /><hr /><br />
                            <strong><?php _e('Customs Values for Shortcodes Map:', EMOSM_TXT_DOMAIN); ?></strong><p></p>
                            <table style="width:100%">
                                <tr>
                                    <td><strong><i>thumbnails</i></strong></td>
                                    <td><?php _e('Display thumbnail: 0 for none, 1 for display', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>baseLayers</i></strong></td>
                                    <td><?php _e('Display baselayers icons map: 0 for none, 1 for display', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>readmore</i></strong></td>
                                    <td><?php _e('Change texte for ReadMore link', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>legend_location</i></strong></td>
                                    <td><?php _e('Display Legend Location: 0 for none, 1 for display', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>legend_events</i></strong></td>
                                    <td><?php _e('Display Legend Events: 0 for none, 1 for display', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>mini_map</i></strong></td>
                                    <td><?php _e('Display Mini-Map on your map', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>cat</i></strong></td>
                                    <td><?php _e('Display Events by Category for Events Map', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>limit</i></strong></td>
                                    <td><?php _e('Change limit of markers for Events Map', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>height</i></strong></td>
                                    <td><?php _e('Change Height for your map', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>map_latitude</i></strong></td>
                                    <td><?php _e('Change Latitude for map centering', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>map_longitude</i></strong></td>
                                    <td><?php _e('Change Longitude for map centering', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>type</i></strong></td>
                                    <td><?php _e("Change type of map: 'location' or 'events'", EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>search</i></strong></td>
                                    <td><?php _e('Display search icon map: 0 for none, 1 for display', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>icon_url</i></strong></td>
                                    <td><?php _e('Enter a URL for custom icon (only for location map)', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>icon_width</i></strong></td>
                                    <td><?php _e('Enter width for custom icon (only for location map)', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>icon_height</i></strong></td>
                                    <td><?php _e('Enter height for custom icon (only for location map)', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>region</i></strong></td>
                                    <td><?php _e('Display Region: 0 for none, 1 for display on marker content', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>state</i></strong></td>
                                    <td><?php _e('Display State: 0 for none, 1 for display on marker content', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><i>country</i></strong></td>
                                    <td><?php _e('Display Country: 0 for none, 1 for display on marker content', EMOSM_TXT_DOMAIN); ?></td>
                                </tr>
                            </table>
             

                            <br /><br /><hr /><br />
                            <table style="width:100%;">
                                <tr>
                                    <td><?php _e('Give me a gift!', EMOSM_TXT_DOMAIN); ?></td>
                                    <td><a href="https://buy.stripe.com/6oE8y46DK69iapa7ss"><img src="<?php echo EMOSM_PLUGIN_URL.'images/btn_stripe.png'; ?>" width="200" /></a><td>
                                    <td><a href="https://www.paypal.com/paypalme/RestezConnectes/"><img src="<?php echo EMOSM_PLUGIN_URL.'images/qrcode.png'; ?>" width="200" /></a><td>
                                </tr>
                            </table>
                            <br /><br /><hr /><br />
                            <strong><?php _e('Delete all settings at plugin desativate?', EMOSM_TXT_DOMAIN); ?></strong><p></p>
                            <input type="radio" name="em_openstreetmap_setting[delete]" value="yes" <?php if( isset($paramMMode['delete']) && $paramMMode['delete'] == 'yes' ) { echo "checked"; } ?> /> <?php _e('Yes', EMOSM_TXT_DOMAIN); ?> <input type="radio" name="em_openstreetmap_setting[delete]" value="no" <?php if( empty($paramMMode['delete']) || (isset($paramMMode['delete']) && $paramMMode['delete'] == 'no') ) { echo "checked"; } ?> /> <?php _e('No', EMOSM_TXT_DOMAIN); ?>
                            <br /><br /><hr /><br />
                            <?php submit_button(); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>

    </div>
    <div style="margin-top:40px;">
        <?php _e('Events Manager OpenStreetMap is brought to you by', EMOSM_TXT_DOMAIN); ?> <a href="https://madeby.restezconnectes.fr/" target="_blank">MadeByRestezConnectes</a> - <?php _e('If you found this plugin useful', EMOSM_TXT_DOMAIN); ?> <a href="https://wordpress.org/support/plugin/events-manager-openstreetmap/reviews/" target="_blank"><?php _e('give it 5 &#9733; on WordPress.org', EMOSM_TXT_DOMAIN); ?></a>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
    wp.codeEditor.initialize($('#emosmstyle'), cm_settings);
    });
</script> 