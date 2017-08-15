(function($){
  function initialize_field($el) {
    mediaUploader();
    annotateInit();
    editAnnotation();
    updateAnnotation();
    deleteAnnotation();
    cancelAnnotationEdit();
    loadExistingAnnotations();

    // On append
    acf.add_action('append', function($el) {
      annotateAppend();
      saveNotification($el);
    });

    $(window).resize(function() {
      loadExistingAnnotations();
    });
  }

  if( typeof acf.add_action !== 'undefined' ) {
    acf.add_action('load append', function( $el ){
      // search $el for fields of type 'image_annotation'
      // acf.get_fields({ type : 'image_annotation'}, $el).each(function(){
        initialize_field( $el );
        // console.log($(this));
      // });
    });
  } else {
    $(document).on('acf/setup_fields', function(e, postbox){
      $(postbox).find('.field[data-field_type="image_annotation"]').each(function() {
        initialize_field($(this));
      });
    });
  }

  function mediaUploader() {
    var mediaUploader;
    var $mediaFieldKey = '';

    $('.image-annotation-attach').click(function(e) {
      $mediaFieldKey = '';

      e.preventDefault();

      // Get id of the specific field
      $mediaFieldKey = $(this).closest('.image-annotation--image');

      // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
        mediaUploader.open();
        return;
      }

      // Extend the wp.media object
      mediaUploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose Image',
        button: {
        text: 'Choose Image'
      }, multiple: false });

      // When a file is selected, grab the URL and set it as the text field's value
      mediaUploader.on('select', function() {
        // Get the data of the chosen image
        attachment = mediaUploader.state().get('selection').first().toJSON();

        // Update the annotated image id field with the id of the image
        $mediaFieldKey.find('.image-annotation--id').val(attachment.id);

        // Clear the annotations field
        $mediaFieldKey.find('.image-annotation--annotations').val('');

        // Clear any existing annotations from the image
        $mediaFieldKey.find('.image-annotation--wrapper .image-annotation--annotation').remove();

        // Update the image with the src of the image
        // If the image is large enough to have a large size, load it, otherwise load the full size image
        var largeImage = attachment.sizes.floorplan;
        if (typeof(largeImage) != "undefined") {
          $mediaFieldKey.find('.image-annotation--image').attr('src', largeImage.url);
        } else {
          $mediaFieldKey.find('.image-annotation--image').attr('src', attachment.sizes.full.url);
        }

        // Make the image visible
        $mediaFieldKey.find('.image-annotation--image').addClass('is-visible');

        // Change the upload image button to say "Replace image"
        $mediaFieldKey.find('.image-annotation-attach').attr('value', 'Replace image');
      });
      // Open the uploader dialog
      mediaUploader.open();
    });
  }

  function saveNotification(element) {
    $element = $(element);
    $element.addClass('is-newly-added');
    $element.find('.acf-field.acf-field-image-annotation').append('<span class="save-notification">Please update page to activate this field</span>');
  }

  function annotateInit() {
    // Initialize the annotated image script on each .annotated_image_wrapper element
    $('.image-annotation--image').each(function(i) {
      $('.image-annotation--wrapper', this).annotatableImage(createAnnotation);
    })
  }

  function annotateAppend() {
    // console.log('annotate append');
    if (jQuery().annotatableImage) {
      $('.image-annotation--wrapper').off('annotatableImage');
    } else {
      $('.image-annotation--wrapper').annotatableImage(createAnnotation);
    }
  }

  function loadExistingAnnotations() {
    // console.log('load existing annotations');
    $('.image-annotation--annotation').remove();

    $('.image-annotation--image').each(function(i) {

      // Parse and load existing annotations
      var annotationsString = $('.image-annotation--annotations', this).val();

      if ( typeof annotationsString != 'undefined' && annotationsString ) {
        // var annotationString = [{"x":0.31,"y":0.17666666666666667}];

        try {
          var annotationsJson = JSON.parse(annotationsString);
        } catch (e) {
          console.log('parse error');
          return false;
        }

        $('.image-annotation--wrapper', this).addAnnotations(function(annotation){
          return $('<div class="image-annotation--annotation"></div><div class="image-annotation--annotation--content"><div class="col-left"><div class="image-annotation--annotation--content--text" placeholder="Annotation text" contentEditable>' + annotation.text + '</div><input type="text" class="image-annotation--annotation--content--label" placeholder="Label" value="' + annotation.label + '" /><label class="image-annotation--annotation--content--label--visibility--label">Create annotation point?</label><input type="checkbox" class="image-annotation--annotation--content--label--visibility" ' + annotation.labelvisibility + ' /></div>');
        }, annotationsJson);
      }
    })
  }

  function createAnnotation() {
    // console.log('create annotation');
    // Add active classes and create annotation element on image
    return $('<div class="image-annotation--annotation is-active is-fresh"></div><div class="image-annotation--annotation--content"><div class="column-left"><div class="image-annotation--annotation--content--text" placeholder="Annotation text" contentEditable></div><input type="text" class="image-annotation--annotation--content--label" placeholder="Label" value="" /><label class="image-annotation--annotation--content--label--visibility--label">Create annotation point?</label><input type="checkbox" class="image-annotation--annotation--content--label--visibility" checked /></div></div>');
  }

  function editAnnotation() {
    // console.log('edit annotation');
    $('.image-annotation--wrapper').on('click','.image-annotation--annotation',function() {
      // Get the key of this specific field
      var $fieldKey = $(this).closest('.acf-field-image-annotation');

      if ( !$(this).hasClass('is-active') ) {
        $(this).addClass('is-active').find('.image-annotation--annotation--content--text').attr('contenteditable', '');
        $('.image-annotation--wrapper, .image-annotation--controls', $fieldKey).addClass('is-active');
        // $imageHeight = $('.image-annotation--wrapper .image-annotation--image', $fieldKey).height();
        // $('.image-annotation--wrapper', $fieldKey).css({"padding-bottom" : $imageHeight+20});
      }
    })
  }

  function updateAnnotation() {
    // console.log('update annotation');
    // When the update button is clicked:
    $('.image-annotation--controls--update').on('click',function() {

      // Get the key of this specific field
      var $fieldKey = $(this).closest('.acf-field-image-annotation');

      // Serialize the annotation data and update record
      serializeMarkers($fieldKey);

      // Remove active class from wrapper and annotation
      $('.image-annotation--wrapper, .image-annotation--controls', $fieldKey).removeClass('is-active').find('.image-annotation--annotation.is-active').removeClass('is-active is-fresh');
    })
  }

  function deleteAnnotation() {
    console.log('delete annotation');
    // When the delete button is clicked:
    $('.image-annotation--controls--delete').on('click', function() {

      // Get the key of this specific field
      var $fieldKey = $(this).closest('.acf-field-image-annotation');

      // Remove the annotation content
      $fieldKey.find('.image-annotation--annotation.is-active + .image-annotation--annotation--content').remove();

      // Remove the annotation
      $fieldKey.find('.image-annotation--annotation.is-active').remove();

      // Remove active class from wrapper and annotation (if annotation was not just added)
      $('.image-annotation--wrapper, .image-annotation--controls', $fieldKey).removeClass('is-active').find('.image-annotation--image.is-active').removeClass('is-active');

      // Serialize the annotation data and update record
      serializeMarkers($fieldKey);
    })
  }

  function cancelAnnotationEdit() {
    // console.log('cancel annotation');
    var $fieldKeyCancel;

    // When the cancel button is clicked:
    $('.image-annotation--controls--cancel').on('click',function() {

      // Get the key of this specific field
      $fieldKeyCancel = $(this).closest('.acf-field-image-annotation');

      // Remove the annotation content
      $fieldKeyCancel.find('.image-annotation--annotation.is-fresh + .image-annotation--annotation--content').remove();

      // If the annotation was just added to the image, remove it
      $fieldKeyCancel.find('.image-annotation--annotation.is-fresh').remove();

      // Remove active class from wrapper and annotation (if annotation was not just added)
      $('.image-annotation--wrapper, .image-annotation--controls',$fieldKeyCancel).removeClass('is-active');

      $fieldKeyCancel.find('.image-annotation--annotation.is-active').removeClass('is-active');

      // Reload annotations from stored value to wipe any edits to contenteditable that were created before cancelling
      loadExistingAnnotations();
    })
  }

  function serializeMarkers($field) {

    // Create array of annotation data
    var annotations = $('.image-annotation--wrapper .image-annotation--annotation', $field).seralizeAnnotations();

    // Create object
    var annotationsObjects = [];

    // Update object with each annotations data, and add text content of each annotation to it's object
    $('.image-annotation--wrapper .image-annotation--annotation',$field).each(function(index, target) {
      // var text = $('.annotated_image_annotation_text',target).html();
      // var label = $('.annotated_image_annotation_label',target).val();
      // var checkedvalue = $('.annotated_image_annotation_label_visibility',target).attr('checked') ? 'checked' : '';
      var text = $('+ .image-annotation--annotation--content', target).find('.image-annotation--annotation--content--text').html();
      // console.log(text);
      var label = $('+ .image-annotation--annotation--content', target).find('.image-annotation--annotation--content--label').val();
      // console.log(label);
      var checkedvalue = $('+ .image-annotation--annotation--content', target).find('.image-annotation--annotation--content-visibility').attr('checked') ? 'checked' : '';
      annotationsObjects.push($.extend({}, annotations[index], {text: text, label: label, labelvisibility: checkedvalue }));
    })

    // JSON.stringify the annotations object, and update the value of the annotations input with the stringified data
    $('.image-annotation--annotations',$field).val(JSON.stringify(annotationsObjects));
  }

})(jQuery);
