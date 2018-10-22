<?php
/*
   Plugin Name: WPQuick Custom Post Type and Forms
   Plugin URI : -
   Description: Manage custom post types and forms to capture and display data
   Version    : 1.0
   Author     : Rakhitha Nimesh
   Author URI: http://www.wpexpertdeveloper.com/
   License: GPLv2 or later
 */
 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, 'wqcpt_activate' );
function wqcpt_activate(){
  wqcpt_manage_property_routes();
  flush_rewrite_rules();  
}

add_action( 'init', 'wqcpt_manage_property_routes' );
function wqcpt_manage_property_routes() {
  add_rewrite_rule( '^property-listing/([^/]+)/?', 'index.php?wpquick_property_actions=$matches[1]', 'top' );
  add_rewrite_tag('%wpquick_property_actions%', '([^&]+)');
}



add_action( 'plugins_loaded', 'wqcpt_plugin_init' );

function wqcpt_plugin_init(){
    global $wqcpt;
    $wqcpt = WPQuick_CPT::instance();
}

if( !class_exists( 'WPQuick_CPT' ) ) {
    
    class WPQuick_CPT{
    
        private static $instance;

        public static function instance() {
            
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPQuick_CPT ) ) {
                self::$instance = new WPQuick_CPT();
                self::$instance->setup_constants();

                self::$instance->includes();
                
                add_action( 'admin_enqueue_scripts',array(self::$instance,'load_admin_scripts'),9);
                add_action( 'wp_enqueue_scripts',array(self::$instance,'load_scripts'),9);

                self::$instance->template_loader = new WQCPT_Template_Loader();
                self::$instance->model_property  = new WQCPT_Model_Property();
              
            }
            return self::$instance;
        }

        public function setup_constants() { 

            if ( ! defined( 'WQCPT_VERSION' ) ) {
                define( 'WQCPT_VERSION', '1.0' );
            }

            if ( ! defined( 'WQCPT_PLUGIN_DIR' ) ) {
                define( 'WQCPT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            if ( ! defined( 'WQCPT_PLUGIN_URL' ) ) {
                define( 'WQCPT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

        }
        
        public function load_scripts(){
            wp_register_style( 'wpwa-front', plugins_url( 'css/style.css', __FILE__ ) );
            wp_enqueue_style( 'wpwa-front' );
           
        }
        
        public function load_admin_scripts(){
            
        }
        
        private function includes() {            
            require_once WQCPT_PLUGIN_DIR . 'classes/class-wqcpt-template-loader.php';
            require_once WQCPT_PLUGIN_DIR . 'classes/class-wqcpt-model-property.php';
        }
    
    }
}



