<?php
/*
  Plugin Name: WQ Rewrite API
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-rewrite-api
  Description: Add Rewrite API features
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */
 
register_activation_hook( __FILE__, 'wqraf_activate' );
function wqraf_activate(){
  wqraf_manage_user_routes();
  flush_rewrite_rules();  
}

add_action( 'init', 'wqraf_manage_user_routes' );
function wqraf_manage_user_routes() {
  add_rewrite_rule( '^user/([^/]+)/?', 'index.php?wpquick_actions=$matches[1]', 'top' );
  add_rewrite_tag('%wpquick_actions%', '([^&]+)');
}

//add_filter( 'query_vars', 'wqraf_manage_user_query_vars' );
function wqraf_manage_user_query_vars( $query_vars ) {
  $query_vars[] = 'wpquick_actions';
  return $query_vars;
}



add_action( 'template_redirect', 'wqraf_front_controller' );
function wqraf_front_controller() {
  global $wp_query;

  $wpquick_actions = isset ( $wp_query->query_vars['wpquick_actions'] ) ? $wp_query->query_vars['wpquick_actions'] : '';
  switch ( $wpquick_actions ) {
    case 'register':
      echo "<h1>REgistration Form</h1>";exit;
      break;
	case 'login':
      echo "<h1>Login Form</h1>";exit;
      break;
  }
}