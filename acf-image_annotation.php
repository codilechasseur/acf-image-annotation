<?php

/*
Plugin Name: Advanced Custom Fields: Image Annotation
Plugin URI: PLUGIN_URL
Description: A plugin to provide image annotation support to ACF.
Version: 1.0.1
Author: Codi Lechasseur
Author URI: https://codilechasseur.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('acf_plugin_image_annotation') ) :

class acf_plugin_image_annotation {

	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct() {

		// vars
		$this->settings = array(
			'version'	=> '1.0.0',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);


		// set text domain
		// https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
		load_plugin_textdomain( 'acf-image_annotation', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );


		// include field
		add_action('acf/include_field_types', 	array($this, 'include_field_types')); // v5
		add_action('acf/register_fields', 		array($this, 'include_field_types')); // v4
		add_action('wp_enqueue_scripts', array($this, 'acf_image_annotation_enqueue_script'));

	}

	function acf_image_annotation_enqueue_script($version = false) {
		wp_enqueue_script( 'acf_image_annotation', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.annotate.js', array('jquery'), $version );
	}


	/*
	*  include_field_types
	*
	*  This function will include the field type class
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	$version (int) major ACF version. Defaults to false
	*  @return	n/a
	*/

	function include_field_types( $version = false ) {

		// support empty $version
		if( !$version ) $version = 4;


		// include
		include_once('fields/acf-image_annotation-v' . $version . '.php');

	}

}


// initialize
new acf_plugin_image_annotation();


// class_exists check
endif;

?>
