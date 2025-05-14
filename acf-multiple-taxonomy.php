<?php

/*
Plugin Name: ACF: Multiple Taxonomy
Plugin URI: https://github.com/game-ryo/acf-multiple-taxonomy
Description: A simple plugin to add a Multiple Taxonomy field. It allows you to select term(s) from multiple taxonomy.
Version: 1.0.9
Author: game-ryo
Author URI: https://profiles.wordpress.org/gameryo/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: acf-multiple-taxonomy
Domain Path: /lang
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('gr_acf_plugin_multiple_taxonomy') ) :

class gr_acf_plugin_multiple_taxonomy {

	// vars
	var $settings;


	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	void
	*  @return	void
	*/

	function __construct() {

		// settings
		// - these will be passed into the field class.
		$this->settings = array(
			'version'	=> '1.0.9',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);


		// include field
		add_action('acf/include_field_types', 	array($this, 'include_field')); // v5
		add_action('acf/register_fields', 		array($this, 'include_field')); // v4
	}


	/*
	*  include_field
	*
	*  This function will include the field type class
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	$version (int) major ACF version. Defaults to false
	*  @return	void
	*/

	function include_field( $version = false ) {

		// support empty $version
		if( !$version ) $version = 4;


		// load textdomain
		load_plugin_textdomain( 'acf-multiple-taxonomy', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );


		// include
		include_once('fields/class-gr-acf-field-multiple-taxonomy-v' . $version . '.php');
	}

}


// initialize
new gr_acf_plugin_multiple_taxonomy();


// class_exists check
endif;

?>
