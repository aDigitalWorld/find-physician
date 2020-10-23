<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://shawn-crigger.herokuapp.com
 * @since      1.0.0
 *
 * @package    Find_A_Provider
 * @subpackage Find_A_Provider/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Find_A_Provider
 * @subpackage Find_A_Provider/public
 * @author     Shawn Crigger <ithippyshawn@gmail.com>
 */
class Find_A_Provider_Public {

   private $devMode = false;

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
    * Initialize the class and set its properties.
    *
    * @since    1.0.0
    * @param      string    $plugin_name       The name of the plugin.
    * @param      string    $version    The version of this plugin.
    */
   public function __construct( $plugin_name, $version ) {
      $this->plugin_name = $plugin_name;
      $this->version = $version;
      if ( TRUE === FIND_A_PROVIDER_DEVELOP ) {
         $this->version = time();
      }
   }

   public function getExtension($css = false) {
      if (!$css) {
         return '.js';
         return ($this->devMode) ? '.js' : '.min.js';
      }
      return ($this->devMode) ? '.css' : '.min.css';
   }
   public function display_map( $atts ) {
      $a = shortcode_atts( array(
         'key' => ''
      ), $atts );
      $file = plugin_dir_path( __FILE__ ) . 'partials/find-a-provider-public-display.php';
      ob_start();
      require($file);
      $html = ob_get_clean();
      return $html;
   }

   /**
    * Register the stylesheets for the public-facing side of the site.
    *
    * @since    1.0.0
    */
   public function enqueue_styles() {
      $ext = $this->getExtension(true);
      wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/find-a-provider-public'.$ext, array(), $this->version, 'all' );
   }

   /**
    * Register the JavaScript for the public-facing side of the site.
    *
    * @since    1.0.0
    */
   public function enqueue_scripts() {
      global $post;
      if(has_shortcode($post->post_content, 'find-a-provider')){
         $ext = $this->getExtension();
         $key = trim(get_option('provider_gmap_api_key'));
         $api = trim(get_option('provider_api_url'));
         $gmaps_url = 'https://maps.googleapis.com/maps/api/js?hl=en&key=' . $key . '&libraries=places';
         wp_enqueue_style('select-ui', 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.default.css' );
         //wp_enqueue_style('select-ui', 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.default.css' );
         wp_enqueue_script('js-cookie', 'https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js', array('jquery') );
         wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.default.min.css' );
         wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js', array('jquery') );
         wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/find-a-provider-public'.$ext, array( 'jquery' ), $this->version, false );
         wp_localize_script( $this->plugin_name, 'MAP_VARS', array(
            'mapKey' => $key,
            'apiURL' => $api,
            'map' => NULL,
            'markers' => [],
            'infoWindow' => NULL,
            'locationSelect' => NULL,
         ));
         wp_enqueue_script('google-maps', $gmaps_url, array( $this->plugin_name ), NULL);
      }
   }

}
