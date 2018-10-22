<?php
/*
  Plugin Name: WQ Shortcode API
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-shortcode-api
  Description: Add Shortcode API features
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */

add_shortcode( 'wpquick_restrict_content', 'wpquick_restrict_content_display' );
function wpquick_restrict_content_display( $atts, $content ){
	$sh_attr = shortcode_atts( array(
		'role' => '',
	), $atts );
	
	if( $sh_attr['role'] != '' && ! current_user_can( $sh_attr['role'] ) ){
		$content = __('You don\'t have permission to view this content','wqsa');
	}
	
	return $content;
}

add_shortcode( 'wpquick_attachment_posts', 'wpquick_attachment_posts_display' );
function wpquick_attachment_posts_display( $atts, $content ){
  global $wpdb;
  $post_attachments_table = $wpdb->prefix.'wpqpa_post_attachments'; 
  $sql  = "SELECT P.post_title,P.guid from $post_attachments_table as PA inner join $wpdb->posts as P
	on P.ID=PA.post_id group by PA.post_id ";

  $result = $wpdb->get_results($sql);

  $html = '';
  if($result){
      foreach ( $result as $key => $value ) {
		$html .= '<a href="'. $value->guid .'">'. $value->post_title .'</a><br/>';       	
      }
  }
  
  return  $html;
}


