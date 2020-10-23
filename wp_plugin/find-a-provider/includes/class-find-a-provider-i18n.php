<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://shawn-crigger.herokuapp.com
 * @since      1.0.0
 *
 * @package    Find_A_Provider
 * @subpackage Find_A_Provider/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Find_A_Provider
 * @subpackage Find_A_Provider/includes
 * @author     Shawn Crigger <ithippyshawn@gmail.com>
 */
class Find_A_Provider_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'find-a-provider',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
