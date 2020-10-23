<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://shawn-crigger.herokuapp.com
 * @since      1.0.0
 *
 * @package    Find_A_Provider
 * @subpackage Find_A_Provider/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Find_A_Provider
 * @subpackage Find_A_Provider/admin
 * @author     Shawn Crigger <ithippyshawn@gmail.com>
 */
class Find_A_Provider_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The options name to be used in this plugin
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	string 		$option_name 	Option name of this plugin
	 */
	private $option_name = 'provider';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Add an options page under the Settings submenu
	 *
	 * @since  1.0.0
	 */
	public function add_options_page() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Find A Provider Settings', $this->plugin_name ),
			__( 'Find A Provider', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);
	}

	public function register_settings()
	{
		// Add a General section
		add_settings_section(
			$this->option_name . '_general',
			__( 'General', $this->plugin_name ),
			array( $this, $this->option_name . '_general_cb' ),
			$this->plugin_name
		);
		add_settings_field(
			$this->option_name . '_gmap_api_key',
			__( 'Google Maps API Key', $this->plugin_name ),
			array( $this, $this->option_name . '_gmap_api_key' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_gmap_api_key' )
		);
		add_settings_field(
			$this->option_name . '_api_url',
			__( 'API URL', $this->plugin_name ),
			array( $this, $this->option_name . '_api_url' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_api_url' )
		);
		register_setting( $this->plugin_name, $this->option_name . '_gmap_api_key', array( $this, $this->option_name . '_sanitize_gmap_api_key' ) );
		register_setting( $this->plugin_name, $this->option_name . '_api_url', array( $this, $this->option_name . '_sanitize_api_url' ) );
	}

	/**
	 * Sanitize the text position value before being saved to database
	 *
	 * @param  string $position $_POST value
	 * @since  1.0.0
	 * @return string           Sanitized value
	 */
	public function provider_sanitize_gmap_api_key( $apikey ) {
		return $apikey;
	}

	/**
	 * Sanitize the text position value before being saved to database
	 *
	 * @param  string $position $_POST value
	 * @since  1.0.0
	 * @return string           Sanitized value
	 */
	public function provider_sanitize_api_url( $url ) {
		$url = filter_var($url, FILTER_SANITIZE_URL);
		return $url;
	}
	/**
	 * Render the google map api key input
	 *
	 * @since  1.0.0
	 */
	public function provider_gmap_api_key() {
		$apikey = get_option( $this->option_name . '_gmap_api_key' );
		echo sprintf('<input type="text" class="regular-text" name="%s_gmap_api_key" id="%s_gmap_api_key" value="%s">', $this->option_name, $this->option_name, $apikey );
	}

	/**
	 * Render the api url input
	 *
	 * @since  1.0.0
	 */
	public function provider_api_url() {
		$url = get_option( $this->option_name . '_api_url' );
		echo sprintf('<input type="text" class="regular-text" name="%s_api_url" id="%s_api_url" value="%s">', $this->option_name, $this->option_name, $url );
	}

	/**
	 * Render the text for the general section
	 *
	 * @since  1.0.0
	 */
	public function provider_general_cb() {
		echo '<p>' . __( 'Please change the settings accordingly.', $this->plugin_name ) . '</p>';
	}

	/**
	 * Render the options page for plugin
	 *
	 * @since  1.0.0
	 */
	public function display_options_page() {
		include_once 'partials/find-a-provider-admin-display.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Find_A_Provider_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Find_A_Provider_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/find-a-provider-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Find_A_Provider_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Find_A_Provider_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/find-a-provider-admin.js', array( 'jquery' ), $this->version, false );

	}

}
