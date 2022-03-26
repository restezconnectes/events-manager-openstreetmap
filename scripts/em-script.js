jQuery(document).ready(function() {

    var custom_uploader;
 
    jQuery('#upload_icon_button').click(function(e) {
 
        e.preventDefault();
 
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
 
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: Data.title,
            button: {
                text: Data.textebutton
            },
            multiple: false
        });
 
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            jQuery('#upload_icon').val(attachment.url);
        });
 
        //Open the uploader dialog
        custom_uploader.open();
 
    });

    jQuery('#upload_cat_icon_button').click(function(e) {
 
        e.preventDefault();
 
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
 
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: Data.title,
            button: {
                text: Data.textebutton
            },
            multiple: false
        });
 
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            jQuery('#categoriescpt-icon-id').val(attachment.id);
            jQuery('#categoriescpt-icon-wrapper').html('<img class="custom_icon_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
            jQuery('#categoriescpt-icon-wrapper .custom_icon_image').attr('src',attachment.url).css('display','block');
        });
 
        //Open the uploader dialog
        custom_uploader.open();
 
    });

    jQuery('#ct_tax_icon_remove').click(function(e) {
 

        jQuery('body').on('click','.ct_tax_icon_remove',function(){
            jQuery('#categoriescpt-icon-id').val('');
            jQuery('#categoriescpt-icon-wrapper').html('<img class="custom_icon_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
          });
              
        // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-categoriescpt-ajax-response
        jQuery(document).ajaxComplete(function(event, xhr, settings) {
        var queryStringArr = settings.data.split('&');
            if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
                var xml = xhr.responseXML;
                $response = $(xml).find('term_id').text();
                if($response!=""){
                // Clear the thumb image
                jQuery('#categoriescpt-image-wrapper').html('');
                jQuery('#categoriescpt-icon-wrapper').html('');
                }
            }
        });

    });

});

