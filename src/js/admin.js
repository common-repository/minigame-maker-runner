$('.kz-mg-runner-upload').click(function () {
    event.preventDefault();

    var frame,
        button = $(this),
        preview = '#' + $(this).attr('data-kz-mg-field') + '-preview',
        input = '#' + $(this).attr('data-kz-mg-field') + '-input';

    // If the media frame already exists, reopen it.
    if (frame) {
        frame.open();
        return;
    }

    // Create a new media frame
    frame = wp.media({
        title: 'Select or Upload Media Of Your Chosen Persuasion',
        button: {
            text: 'Use this media'
        },
        multiple: false
    });

    // When an image is selected in the media frame...
    frame.on('select', function () {

        // Get media attachment details from the frame state
        var attachment = frame.state().get('selection').first().toJSON();

        // Send the attachment URL to our custom image input field.
        $(preview).attr('src', attachment.url)
        $(preview).attr('alt', '')

        // Send the attachment id to our hidden input
        $(input).val(attachment.url);
    });

    // Finally, open the modal on click
    frame.open();

});
