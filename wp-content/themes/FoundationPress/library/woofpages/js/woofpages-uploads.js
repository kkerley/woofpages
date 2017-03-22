jQuery(document).ready(function($) {
    // $('#woofpages_rescue_custom_logo_upload_button').click(function() {
    //     tb_show('Upload a logo', 'media-upload.php?referer=woofpages_settings_panel&type=image&TB_iframe=true&post_id=0', false);
    //     window.send_to_editor = function(html) {
    //         var image_url = $(html).attr('src');
    //         $('#woofpages_rescue_custom_logo_text').val(image_url);
    //         tb_remove();
    //         $('#woofpages_uploaded_logo_preview img').attr('src',image_url);
    //
    //         $('#woofpages_submit_options_form').trigger('click');
    //     }
    //     return false;
    // });

    // Uploading files
    var file_frame;
    var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id

    $('#woofpages_rescue_custom_logo_upload_button').on('click', function( event ){
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            // Set the post ID to what we want
            file_frame.uploader.uploader.param( 'post_id', 0 );
            // Open frame
            file_frame.open();
            return;
        } else {
            // Set the wp.media post id so the uploader grabs the ID we want when initialised
            wp.media.model.settings.post.id = 0;
        }
        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select a image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false	// Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            // Do something with attachment.id and/or attachment.url here
            $( '#woofpages_uploaded_logo_preview img' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
            $( '#woofpages_rescue_custom_logo_text' ).val( attachment.url );
            // Restore the main post ID
            wp.media.model.settings.post.id = wp_media_post_id;
            $('#woofpages_submit_options_form').trigger('click');
        });
        // Finally, open the modal
        file_frame.open();
    });
    // Restore the main ID when the add media button is pressed
    $( 'a.add_media' ).on( 'click', function() {
        wp.media.model.settings.post.id = wp_media_post_id;
    });

});