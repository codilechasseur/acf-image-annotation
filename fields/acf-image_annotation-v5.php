<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_field_image_annotation') ) :


class acf_field_image_annotation extends acf_field {


  /*
  *  __construct
  *
  *  This function will setup the field type data
  *
  *  @type	function
  *  @date	5/03/2014
  *  @since	5.0.0
  *
  *  @param	n/a
  *  @return	n/a
  */

  function __construct( $settings ) {

    /*
    *  name (string) Single word, no spaces. Underscores allowed
    */

    $this->name = 'image_annotation';


    /*
    *  label (string) Multiple words, can include spaces, visible when selecting a field type
    */

    $this->label = __('Image Annotation', 'acf-image_annotation');


    /*
    *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
    */

    $this->category = 'content';


    /*
    *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
    *  var message = acf._e('image_annotation', 'error');
    */

    $this->l10n = array(
      'error'	=> __('Your annotation could not be saved.', 'acf-image_annotation'),
    );


    /*
    *  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
    */

    $this->settings = $settings;


    // do not delete!
      parent::__construct();

  }


  /*
  *  render_field()
  *
  *  Create the HTML interface for your field
  *
  *  @param	$field (array) the $field being rendered
  *
  *  @type	action
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	$field (array) the $field being edited
  *  @return	n/a
  */

  function render_field( $field ) {

    // vars
    $uploader = acf_get_setting('uploader');


    // enqueue
    if( $uploader == 'wp' ) {

      acf_enqueue_uploader();

    }

    // vars
    $url = '';
    $alt = '';
    $div = array(
      'class'					=> 'image-annotation--image acf-cf',
      'data-uploader'			=> $uploader,
    );

    /*
    *  Review the data of $field.
    *  This will show what data is available
    */

    // echo '<pre>';
    // 	print_r( $field );
    // echo '</pre>';

    // has value?
    if( $field['value'][0] ) {
      // update vars
      $url = wp_get_attachment_image_src($field['value'][0], 'full');
      $alt = get_post_meta($field['value'], '_wp_attachment_image_alt', true);

      // url exists
      if( $url ) $url = $url[0];

      // url exists
      if( $url ) {
        $div['class'] .= ' has-value';
      }
    }


    $buttonText = ($url  ? 'Replace image' : 'Upload image');
    ?>
<div <?php acf_esc_attr_e( $div ); ?>>
  <?php
  acf_hidden_input(
    array(
      'name' => $field['name'],
      'value' => $field['value'],
    )
  );
  ?>

  <div class="acf-hidden">
    <fieldset>
      <input class="image-annotation--value" type="text" name="<?php echo esc_attr($field['name']) ?>" value="" />
      <input class="image-annotation--id" type="text" name="<?php echo esc_attr($field['name']) ?>[0]" value="<?php echo esc_attr($field['value'][0]) ?>" />
      <input class="image-annotation--annotations" type="text" name="<?php echo esc_attr($field['name']) ?>[1]" value="<?php echo esc_attr($field['value'][1]) ?>" />
    </fieldset>
  </div>

  <input class="button image-annotation-attach" type="button"  value="<?php echo $buttonText; ?>" />

  <div class="image-annotation--wrapper" data-name="<?php echo esc_attr($field['name']) ?>">
    <img class="image-annotation--img" src="<?php echo $url; ?>" />
  </div>

  <!-- Annotation controls -->
  <div class="image-annotation--controls">
    <div class="button image-annotation--controls--update">Save Annotation</div>
    <div class="button image-annotation--controls--delete">Delete Annotation</div>
    <div class="image-annotation--controls--cancel">Cancel</div>
  </div>
</div>
    <?php
  }


  /*
  *  input_admin_enqueue_scripts()
  *
  *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
  *  Use this action to add CSS + JavaScript to assist your render_field() action.
  *
  *  @type	action (admin_enqueue_scripts)
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	n/a
  *  @return	n/a
  */


  function input_admin_enqueue_scripts() {

    // vars
    $url = $this->settings['url'];
    $version = $this->settings['version'];

    // register & include JS
    wp_register_script( 'acf-input-image_annotation', "{$url}assets/js/image-annotation.js", array('acf-input'), $version );
    wp_enqueue_script('acf-input-image_annotation');

    wp_register_script( 'jquery-annotate', "{$url}assets/js/jquery.annotate.js", array(), 1 );
    wp_enqueue_script('jquery-annotate');

    // register & include CSS
    wp_register_style( 'acf-input-image_annotation', "{$url}assets/css/image-annotation.css", array('acf-input'), $version );
    wp_enqueue_style('acf-input-image_annotation');

  }

  /*
  *  input_admin_head()
  *
  *  This action is called in the admin_head action on the edit screen where your field is created.
  *  Use this action to add CSS and JavaScript to assist your render_field() action.
  *
  *  @type	action (admin_head)
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	n/a
  *  @return	n/a
  */

  /*

  function input_admin_head() {



  }

  */


  /*
     *  input_form_data()
     *
     *  This function is called once on the 'input' page between the head and footer
     *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
     *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
     *  seen on comments / user edit forms on the front end. This function will always be called, and includes
     *  $args that related to the current screen such as $args['post_id']
     *
     *  @type	function
     *  @date	6/03/2014
     *  @since	5.0.0
     *
     *  @param	$args (array)
     *  @return	n/a
     */

     /*

     function input_form_data( $args ) {



     }

     */


  /*
  *  input_admin_footer()
  *
  *  This action is called in the admin_footer action on the edit screen where your field is created.
  *  Use this action to add CSS and JavaScript to assist your render_field() action.
  *
  *  @type	action (admin_footer)
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	n/a
  *  @return	n/a
  */

  /*

  function input_admin_footer() {



  }

  */


  /*
  *  field_group_admin_enqueue_scripts()
  *
  *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
  *  Use this action to add CSS + JavaScript to assist your render_field_options() action.
  *
  *  @type	action (admin_enqueue_scripts)
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	n/a
  *  @return	n/a
  */

  /*

  function field_group_admin_enqueue_scripts() {

  }

  */


  /*
  *  field_group_admin_head()
  *
  *  This action is called in the admin_head action on the edit screen where your field is edited.
  *  Use this action to add CSS and JavaScript to assist your render_field_options() action.
  *
  *  @type	action (admin_head)
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	n/a
  *  @return	n/a
  */

  /*

  function field_group_admin_head() {

  }

  */


  /*
  *  load_value()
  *
  *  This filter is applied to the $value after it is loaded from the db
  *
  *  @type	filter
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	$value (mixed) the value found in the database
  *  @param	$post_id (mixed) the $post_id from which the value was loaded
  *  @param	$field (array) the field array holding all the field options
  *  @return	$value
  */

  /*

  function load_value( $value, $post_id, $field ) {

    return $value;

  }

  */


  /*
  *  update_value()
  *
  *  This filter is applied to the $value before it is saved in the db
  *
  *  @type	filter
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	$value (mixed) the value found in the database
  *  @param	$post_id (mixed) the $post_id from which the value was loaded
  *  @param	$field (array) the field array holding all the field options
  *  @return	$value
  */



  function update_value( $value, $post_id, $field ) {

    return $value;

  }




  /*
  *  format_value()
  *
  *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
  *
  *  @type	filter
  *  @since	3.6
  *  @date	23/01/13
  *
  *  @param	$value (mixed) the value which was loaded from the database
  *  @param	$post_id (mixed) the $post_id from which the value was loaded
  *  @param	$field (array) the field array holding all the field options
  *
  *  @return	$value (mixed) the modified value
  */

  /*

  function format_value( $value, $post_id, $field ) {

    // bail early if no value
    if( empty($value) ) {

      return $value;

    }


    // apply setting
    if( $field['font_size'] > 12 ) {

      // format the value
      // $value = 'something';

    }


    // return
    return $value;
  }

  */


  /*
  *  validate_value()
  *
  *  This filter is used to perform validation on the value prior to saving.
  *  All values are validated regardless of the field's required setting. This allows you to validate and return
  *  messages to the user if the value is not correct
  *
  *  @type	filter
  *  @date	11/02/2014
  *  @since	5.0.0
  *
  *  @param	$valid (boolean) validation status based on the value and the field's required setting
  *  @param	$value (mixed) the $_POST value
  *  @param	$field (array) the field array holding all the field options
  *  @param	$input (string) the corresponding input name for $_POST value
  *  @return	$valid
  */

  /*

  function validate_value( $valid, $value, $field, $input ){

    // Basic usage
    if( $value < $field['custom_minimum_setting'] )
    {
      $valid = false;
    }


    // Advanced usage
    if( $value < $field['custom_minimum_setting'] )
    {
      $valid = __('The value is too little!','acf-image_annotation'),
    }


    // return
    return $valid;

  }

  */


  /*
  *  delete_value()
  *
  *  This action is fired after a value has been deleted from the db.
  *  Please note that saving a blank value is treated as an update, not a delete
  *
  *  @type	action
  *  @date	6/03/2014
  *  @since	5.0.0
  *
  *  @param	$post_id (mixed) the $post_id from which the value was deleted
  *  @param	$key (string) the $meta_key which the value was deleted
  *  @return	n/a
  */

  /*

  function delete_value( $post_id, $key ) {



  }

  */


  /*
  *  load_field()
  *
  *  This filter is applied to the $field after it is loaded from the database
  *
  *  @type	filter
  *  @date	23/01/2013
  *  @since	3.6.0
  *
  *  @param	$field (array) the field array holding all the field options
  *  @return	$field
  */

  /*

  function load_field( $field ) {

    return $field;

  }

  */


  /*
  *  update_field()
  *
  *  This filter is applied to the $field before it is saved to the database
  *
  *  @type	filter
  *  @date	23/01/2013
  *  @since	3.6.0
  *
  *  @param	$field (array) the field array holding all the field options
  *  @return	$field
  */

  /*

  function update_field( $field ) {

    return $field;

  }

  */


  /*
  *  delete_field()
  *
  *  This action is fired after a field is deleted from the database
  *
  *  @type	action
  *  @date	11/02/2014
  *  @since	5.0.0
  *
  *  @param	$field (array) the field array holding all the field options
  *  @return	n/a
  */

  /*

  function delete_field( $field ) {



  }

  */


}


// initialize
new acf_field_image_annotation( $this->settings );


// class_exists check
endif;

?>
