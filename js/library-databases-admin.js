/**
 * Adds functionality to our choose image and remove image buttons using the
 * wp.media JS api.
 *
 * @see https://codex.wordpress.org/Javascript_Reference/wp.media
 */
function addImageUploadFunctionality() {

  var hiddenField  = jQuery('#term_meta\\[image\\]');
  var removeButton = jQuery('#remove-image-button');
  var chooseButton = jQuery('#choose-image-button');
  var imageWrapper = jQuery('#lib-databases-thumbnail');

  if (hiddenField.val().trim()) {
    removeButton.show();
  }

  removeButton.click(function() {
    imageWrapper.html('');
    hiddenField.val('');
    removeButton.hide();
    return false;
  });

  chooseButton.click(function() {
    var customUploader = wp.media.frames.file_frame = wp.media({
      title: 'Choose Image',
      library: { type: 'image' },
      button: { text: 'Choose Image' },
      multiple: false
    });

    // set selected image on open
    customUploader.on('open', function() {
      var selection = customUploader.state().get('selection');
      var id = hiddenField.val();
      var attachment = wp.media.attachment(id);
      attachment.fetch();
      selection.add( attachment ? [ attachment ] : [] );
    });

    // get selected image on select
    customUploader.on('select', function() {
      var attachment = customUploader.state().get('selection').first().toJSON();
      hiddenField.val(attachment.id);
      var thumb = jQuery('<img>').attr('src', attachment.url);
      imageWrapper.html(thumb);
      removeButton.show();
    });

    customUploader.open();
  });
}
