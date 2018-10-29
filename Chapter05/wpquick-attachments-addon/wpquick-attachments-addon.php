<?php
/*
  Plugin Name: WPQAA Attachments Addons
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-attachments-addon
  Description: Addon features for restricting attachments and count downloads
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */
  
// Exit if accessed directly
if( !defined( "ABSPATH" ) ) exit;

if ( ! defined( 'WPQAA_PLUGIN_DIR' ) ) {
  define( 'WPQAA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPQAA_PLUGIN_URL' ) ) {
  define( 'WPQAA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

add_filter( 'wpqpa_post_attachment_list_item', 'wpqaa_post_attachment_list_item' , 10 ,2 ); 
function wpqaa_post_attachment_list_item( $display, $file_data ){
  $upload_dir = wp_upload_dir(); 

  $file_dir =  $upload_dir['basedir'] . $file_data->file_path; 
  $file_mime_type = mime_content_type( $file_dir ); 
  if( $file_mime_type == 'application/pdf' ){ 
	if( !is_user_logged_in() || ( is_user_logged_in() && current_user_can('subscriber') ) ){
      $display = '';
    }
  }
  return $display;
}

add_action( 'wpqpa_before_download_post_attachment', 'wpqaa_before_download_post_attachment' );
function wpqaa_before_download_post_attachment( $data ){
 $post_id = $data['post_id'];
 if( is_user_logged_in() ){
   $count = get_post_meta( $post_id, 'wpqaa_member_download_count',true );
   update_post_meta( $post_id, 'wpqaa_member_download_count', $count + 1);
 }else{
   $count = get_post_meta( $post_id, 'wpqaa_guest_download_count', true );
   update_post_meta( $post_id, 'wpqaa_guest_download_count', $count + 1 );
 }
}

