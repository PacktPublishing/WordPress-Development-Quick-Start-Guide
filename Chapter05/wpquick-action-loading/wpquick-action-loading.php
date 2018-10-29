<?php
/*
  Plugin Name: WPQAL Action Loading
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-after-post
  Description: Add dynamic content after the post
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */


/* Scenario 1 - Testing */
// echo WPQPA_PLUGIN_URL;exit;

// add_action( 'plugins_loaded', 'wpqal_plugins_loaded_action' );
// function wpqal_plugins_loaded_action() {
//   echo WPQPA_PLUGIN_URL;
// }


/* Scenario 2 - Testing */
// add_action( 'init', 'wpqal_init_action' );
// function wpqal_init_action() {
//   global $post;
//   print_r($post->ID);exit;
// }

// add_action( 'wp', 'wpqal_wp_action' );
// function wpqal_wp_action() {
//   global $post; 
//   print_r($post->ID);exit;
// }

/* Scenario 3 - Testing */
// add_action( 'pre_get_posts', 'wpqal_pre_get_posts_action' );
// function wpqal_pre_get_posts_action( $query ) {
//   global $wp_query; 
//   print_r($wp_query);  
// }

// add_action( 'wp', 'wpqal_wp_action' );
// function wpqal_wp_action() {
//   global $wp_query;
//   print_r($wp_query);  
// }