<?php
/*
  Plugin Name: WPQAPC After Post Content
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-after-post
  Description: Add dynamic content after the post
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */

// Exit if accessed directly
if( !defined( "ABSPATH" ) ) exit;

add_filter( 'the_content', 'wpqapc_file_attachment_display' );
function wpqapc_file_attachment_display( $content ){
  global $post;
  if( is_singular( 'post' ) ){
      $after_content = '<div id="wpquick-article-ads" style="padding:20px;text-align:center;font-size:20px;background:red;color:#FFF;">
                        GET MEMBERSHIP WITH 30% DISCOUNT      </div>';
      return $content . $after_content;
  }
  return $content;
}