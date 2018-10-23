<?php
/*
   Plugin Name: WPQuick Elementor Components
   Plugin URI : -
   Description: Used for creating elementor components
   Version    : 1.0
   Author     : Rakhitha Nimesh
   Author URI: http://www.wpexpertdeveloper.com/
   License: GPLv2 or later
 */
 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;
    
class WPQuick_EC{
    
    private static $instance = null;

    public static function get_instance() {
        if ( ! self::$instance )
            self::$instance = new self;
        return self::$instance;
    }

    public function init() {
        global $wqec;
        $this->setup_constants();
        $this->includes();
        
        add_action( 'admin_enqueue_scripts',array( $this,'load_admin_scripts'),9);
        add_action( 'wp_enqueue_scripts',array( $this,'load_scripts'),9);

        $wqec = new stdClass;
        add_shortcode( 'wqec_image_slider', array( $this, 'image_slider') );
        add_action( 'elementor/widgets/widgets_registered', array( $this, 'widgets_registered' ) );

          
    }

    public function setup_constants() { 

        if ( ! defined( 'WQEC_VERSION' ) ) {
            define( 'WQEC_VERSION', '1.0' );
        }

        if ( ! defined( 'WQEC_PLUGIN_DIR' ) ) {
            define( 'WQEC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        }

        if ( ! defined( 'WQEC_PLUGIN_URL' ) ) {
            define( 'WQEC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }

    }
    
    public function load_scripts(){ 
        
        wp_register_style( 'wqec-slider', WQEC_PLUGIN_URL . 'css/bjqs.css' );
        wp_register_style( 'wqec-demo', WQEC_PLUGIN_URL . 'css/demo.css' );
        wp_register_script( 'wqec-sliders', WQEC_PLUGIN_URL . 'js/bjqs-1.3.js', array('jquery') );
        wp_register_script( 'wqec-front', WQEC_PLUGIN_URL . 'js/wqec-front.js', array('jquery','wqec-sliders') );
        wp_enqueue_style( 'wqec-slider' );    
        wp_enqueue_style( 'wqec-demo' );  
        wp_enqueue_script( 'wqec-slider' );
        wp_enqueue_script( 'wqec-front' );
    }

    public function load_admin_scripts(){ }
    
    private function includes() { }

    public function widgets_registered() {
        if(defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')){
            require_once WQEC_PLUGIN_DIR . 'widgets/wqec-slider-element.php';
        }
    }

    public function image_slider( $atts, $content ){
        $sh_attr = shortcode_atts( array(
            'width' => '520',
            'height' => '320',
            'slider_images' => ''
        ), $atts );       
		extract($sh_attr);

        $upload_dir = wp_upload_dir();

        $display = '<div class="banner-fade" data-width="'.$width.'" data-height="'.$height.'">  
                        <ul class="bjqs">';

        $sh_attr['slider_images'] = explode( ',' , $sh_attr['slider_images'] );

        foreach($sh_attr['slider_images'] as $attach_id){
            if($attach_id != ''){
                $attachment = wp_get_attachment_metadata( $attach_id );
                $display .= '<li><img src="' . $upload_dir['baseurl'] . '/'. $attachment['file'] . '"  /></li>';
            }
        }
        
                            
        $display .= '   </ul>
                    <div>';

        return $display;
    }
    
}


WPQuick_EC::get_instance()->init();

