<?php

/**
 * Plugin class
 **/

if ( ! class_exists( 'EM_TAX_META' ) ) {

class EM_TAX_META {

  public function __construct() {
    //

  }
 
 /*
  * Initialize the class and start calling our hooks and filters
  * @since 1.0.0
 */
 public function init() {
    add_action( 'event-categories_add_form_fields', array ( $this, 'add_categories_icon' ), 10, 2 );
    add_action( 'created_event-categories', array ( $this, 'save_categories_icon' ), 10, 2 );
    add_action( 'event-categories_edit_form_fields', array ( $this, 'update_categories_icon' ), 10, 2 );
    add_action( 'edited_event-categories', array ( $this, 'updated_categories_icon' ), 10, 2 );
   add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
 }

public function load_media() {
    wp_enqueue_script('media-upload');
    wp_enqueue_media();
    wp_enqueue_script('thickbox');

    wp_register_script('em-upload', EMOSM_PLUGIN_URL.'scripts/em-script.js', array('jquery','media-upload','thickbox'));
    wp_enqueue_script('em-upload');

    // Now we can localize the script with our data.
    wp_localize_script( 'em-upload', 'Data', array(
      'textebutton'  =>  __( 'Choose This Icon', EMOSM_TXT_DOMAIN ),
      'title'  => __( 'Choose Icon', EMOSM_TXT_DOMAIN ),
    ) );
}
 
 /*
  * Add a form field in the new categoriescpt page
  * @since 1.0.0
 */
 public function add_categories_icon ( $taxonomy ) { ?>
    <div class="form-field term-group">
        <label for="categoriescpt-icon-id"><?php _e('Map Icon', EMOSM_TXT_DOMAIN); ?></label>
        <input type="hidden" id="categoriescpt-icon-id" name="categoriescpt-icon-id" class="custom_media_url" value="">
        <div id="categoriescpt-icon-wrapper"></div>
        <p>
          <button type="button" id="upload_cat_icon_button" class="button button-secondary" OnClick="this.blur();"><?php _e( 'Change Icon', EMOSM_TXT_DOMAIN ); ?></button>
            <input type="button" class="button button-secondary ct_tax_icon_remove" id="ct_tax_icon_remove" name="ct_tax_icon_remove" value="<?php _e( 'Remove Icon', EMOSM_TXT_DOMAIN ); ?>" />
        </p>
    </div>
 <?php
 }
 
 /*
  * Save the form field
  * @since 1.0.0
 */
 public function save_categories_icon ( $term_id, $tt_id ) {

  if( isset( $_POST['categoriescpt-icon-id'] ) && '' !== $_POST['categoriescpt-icon-id'] ){
    $icon = $_POST['categoriescpt-icon-id'];
    add_term_meta( $term_id, 'em-categories-icon-id', $icon, true );
  }
  $genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate('events', '', '', 1);
  $genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate('location', '', '', 1);
 }
 
 /*
  * Edit the form field
  * @since 1.0.0
 */
 public function update_categories_icon ( $term, $taxonomy ) { ?>
    <tr class="form-field term-group-wrap">
        <th scope="row">
            <label for="categoriescpt-icon-id"><?php _e( 'Icon', EMOSM_TXT_DOMAIN ); ?></label>
        </th>
        <td>
        <?php $icon_id = get_term_meta ( $term -> term_id, 'em-categories-icon-id', true ); ?>
        <input type="hidden" id="categoriescpt-icon-id" name="categoriescpt-icon-id" value="<?php echo $icon_id; ?>">
        <div id="categoriescpt-icon-wrapper">
            <?php if ( $icon_id ) { ?>
            <?php echo wp_get_attachment_image ( $icon_id, 'thumbnail' ); ?>
            <?php } ?>
        </div>
        <p>
            <button type="button" id="upload_cat_icon_button" class="button button-secondary" OnClick="this.blur();"><?php _e( 'Change Icon', EMOSM_TXT_DOMAIN ); ?></button>
            <input type="button" class="button button-secondary ct_tax_icon_remove" id="ct_tax_icon_remove" name="ct_tax_icon_remove" value="<?php _e( 'Remove Icon', EMOSM_TXT_DOMAIN ); ?>" />
        </p>
        </td>
    </tr>
 <?php
 }

/*
 * Update the form field value
 * @since 1.0.0
 */
 public function updated_categories_icon ( $term_id, $tt_id ) {

    if( isset( $_POST['categoriescpt-icon-id'] ) && '' !== $_POST['categoriescpt-icon-id'] ){
        $icon = $_POST['categoriescpt-icon-id'];
        update_term_meta ( $term_id, 'em-categories-icon-id', $icon );
    } else {
        update_term_meta ( $term_id, 'em-categories-icon-id', EMOSM_TXT_DOMAIN );
    }

    $genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate('events', '', '', 1);
    $genereFile = EM_Openstreetmap_Class::em_openstreetmap_generate('location', '', '', 1);

 }


}
 
$EM_TAX_META = new EM_TAX_META();
$EM_TAX_META -> init();
 
}