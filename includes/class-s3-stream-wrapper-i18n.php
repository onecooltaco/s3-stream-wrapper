<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/onecooltaco/s3-stream-wrapper
 * @since      1.0.0
 *
 * @package    S3_Stream_Wrapper
 * @subpackage S3_Stream_Wrapper/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    S3_Stream_Wrapper
 * @subpackage S3_Stream_Wrapper/includes
 * @author     Jeremy Leggat <jleggat@asu.edu>
 */
class S3_Stream_Wrapper_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			's3-stream-wrapper',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
