<?php
/*
   Plugin Name: WPQuick Key Modules
   Plugin URI : -
   Description: Used to for improving usability of various frontend and backend features
   Version    : 1.0
   Author     : Rakhitha Nimesh
   Author URI: http://www.wpexpertdeveloper.com/
   License: GPLv2 or later
 */
 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;
    
class WPQuick_KM{

    private static $instance;

    public static function instance() {
            
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPQuick_KM ) ) {
            self::$instance = new WPQuick_KM();
            self::$instance->setup_constants();

            self::$instance->includes();
            
            
            add_action( 'wp_enqueue_scripts',array(self::$instance,'load_scripts'),9);
			add_action( 'admin_enqueue_scripts',array(self::$instance,'load_admin_scripts'),9);

            self::$instance->template_loader = new WQKM_Template_Loader();
            self::$instance->ui_components  = new WQKM_UI_Components();
            self::$instance->admin_features  = new WQKM_Admin_Features();
        }
        return self::$instance;
    }

    public function setup_constants() { 

        if ( ! defined( 'WQKM_VERSION' ) ) {
            define( 'WQKM_VERSION', '1.0' );
        }

        if ( ! defined( 'WQKM_PLUGIN_DIR' ) ) {
            define( 'WQKM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }

        if ( ! defined( 'WQKM_PLUGIN_URL' ) ) {
            define( 'WQKM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }

    }
    
    public function load_scripts(){
        wp_register_style( 'wqkm-slider', WQKM_PLUGIN_URL . 'css/bjqs.css' );
        wp_register_style( 'wqkm-slider-demo', WQKM_PLUGIN_URL . 'css/demo.css' );
        wp_register_script( 'wqkm-slider', WQKM_PLUGIN_URL . 'js/bjqs-1.3.js', array('jquery') );
        wp_register_script( 'wqkm-front', WQKM_PLUGIN_URL . 'js/wqkm-front.js', array('jquery','wqkm-slider') );
        
        wp_register_style('wqkm-jquery-ui-style', WQKM_PLUGIN_URL . 'css/jquery-ui.css', false, null);
        wp_register_script( 'wqkm-accordion', WQKM_PLUGIN_URL . 'js/wqkm-accordion.js', array('jquery','jquery-ui-accordion') );

    }
    
    public function load_admin_scripts(){
        wp_register_style( 'wqkm-admin', WQKM_PLUGIN_URL . 'css/wqkm-admin.css' );
        wp_enqueue_style( 'wqkm-admin' );

        
        
        wp_register_script( 'wqkm-admin', WQKM_PLUGIN_URL . 'js/wqkm-admin.js', array('jquery') );
        wp_enqueue_script( 'wqkm-admin' );
        
        $custom_js_strings = array(        
            'AdminAjax' => admin_url('admin-ajax.php'),
            'Messages'  => array(
                                'fileRequired' => __('File is required.','wqkm'),   
                            ),
            'nonce' => wp_create_nonce('wqkm-private-admin'),
        );

        wp_localize_script( 'wqkm-admin', 'WQKMAdmin', $custom_js_strings );
    }
    
    private function includes() {            
        require_once WQKM_PLUGIN_DIR . 'classes/class-wqkm-template-loader.php';
        require_once WQKM_PLUGIN_DIR . 'classes/class-wqkm-ui-components.php';
        require_once WQKM_PLUGIN_DIR . 'classes/class-wqkm-admin-features.php';
        require_once WQKM_PLUGIN_DIR . 'classes/class-wqkm-product-slider-widget.php';
    }
}


add_action( 'plugins_loaded', 'wqkm_plugin_init' );

function wqkm_plugin_init(){
    global $wqkm;
    $wqkm = WPQuick_KM::instance();
}

