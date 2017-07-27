jQuery(document).ready(function(){

    // Uploading files
    var file_frame;
    var wp_media_post_id = wp.media.model.settings.post.id;
    jQuery('.upload-button').each(function() {
        jQuery(this).on('click', function (e) {
            e.preventDefault();

            $this = jQuery('#'+jQuery(this).attr('id'));

            // If the media frame already exists, reopen it.
            if (file_frame) {
                // Open frame
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select a badge icon.',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            // When an image is selected, run a callback.
            file_frame.on('select', function () {
                var attachment = file_frame.state().get('selection').first().toJSON();
                // Do something with attachment.id and/or attachment.url here
                $this.prevUntil('.image-preview').prev().attr('src', attachment.url);
                $this.prevUntil('[id^=url_]').prev().val(attachment.url);
                $this.next('input[type="hidden"]').val(attachment.id);

                // Restore the main post ID
                wp.media.model.settings.post.id = wp_media_post_id;
            });

            // Finally, open the modal
            file_frame.open();

            // Restore the main ID when the add media button is pressed
            jQuery('a.add_media').on('click', function () {
                wp.media.model.settings.post.id = wp_media_post_id;
            });
        });
    });
});