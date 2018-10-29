<?php
/*
  Plugin Name: WPQPA Post Attachments
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-post-attachments
  Description: Add features for uploading atatchments to posts and download from frontend
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */

// Exit if accessed directly
if( !defined( "ABSPATH" ) ) exit;

register_activation_hook( __FILE__, 'wpqpa_activate' );
function wpqpa_activate(){
  global $wpdb,$wp_roles;

  $table_attachments = $wpdb->prefix . 'wpqpa_post_attachments';
  $sql_attachments = "CREATE TABLE IF NOT EXISTS $table_attachments (
      id int(11) NOT NULL AUTO_INCREMENT,
      file_name varchar(255) NOT NULL,
      user_id int(11) NOT NULL,
      post_id int(11) NOT NULL,
      file_path longtext NOT NULL,
      updated_at datetime NOT NULL,
      uploaded_file_name varchar(255) NOT NULL,
      PRIMARY KEY (id)
    );";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql_attachments );

  $default_headers = array('Version' => 'Version');
  $plugin_data = get_file_data(__FILE__, $default_headers, 'plugin');
  update_option( 'wpqpa_version',$plugin_data['Version'] );  
}

if ( ! defined( 'WPQPA_PLUGIN_DIR' ) ) {
  define( 'WPQPA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPQPA_PLUGIN_URL' ) ) {
  define( 'WPQPA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

add_action( 'wp_loaded', 'wpqpa_upgrade_process' );
function wpqpa_upgrade_process(){
  $default_headers = array('Version' => 'Version');
  $plugin_data = get_file_data( __FILE__, $default_headers, 'plugin' );

  $stored_version = get_option('wpqpa_version');
  $current_version = $plugin_data['Version'];

  if ( !$stored_version && $current_version ) {
      update_option( 'wpqpa_version', $plugin_data['Version'] ); 
  }

  if ( version_compare( $current_version , $stored_version ) >= 0 ) {
    update_option( 'wpqpa_max_upload_limit',20 );
  }

  update_option( 'wpqpa_version', $plugin_data['Version'] ); 
}

add_action( 'admin_enqueue_scripts', 'wpqpa_load_scripts',9 );
add_action( 'wp_enqueue_scripts', 'wpqpa_load_scripts',9 );
function wpqpa_load_scripts(){
  wp_register_style( 'wpqpa_css', WPQPA_PLUGIN_URL . 'css/wpqpa.css' );
  wp_enqueue_style( 'wpqpa_css' );
}


add_action( 'add_meta_boxes', 'wpqpa_post_attachments_meta_box' );
function wpqpa_post_attachments_meta_box(){
  add_meta_box(
    'wpqpa-post-attachments',
    __( 'Post attachments', 'wpqpa' ),
    'display_post_attachments_meta_box',
    'post',
    'normal',
    'high'
  );
}

function display_post_attachments_meta_box( $post, $metabox ){
  global $wpdb;
  $display = '<div class="wpqpa-files-panel" >
                <div class="wpqpa-files-add-form" >  
                  <div class="wpqpa-files-msg" style="display:none" ></div>
                  <div class="wpqpa-files-add-form-row">
                    <div class="wpqpa-files-add-form-label">'.__("File Title","wpqpa").'</div>
                    <div class="wpqpa-files-add-form-field">
                        <input type="text" class="wpqpa-file-name" name="wpqpa_file_name" />
                    </div>                    
                  </div>                      
                  <div class="wpqpa-files-add-form-row">
                    <div class="wpqpa-files-add-form-label">'.__("File","wpqpa").'</div>
                    <div class="wpqpa-files-add-form-field">
                        <input type="file" class="wpqpa-file" name="wpqpa_file" />
                    </div>                    
                  </div>      
                    
                  <div class="wpqpa-clear"></div>
                </div>';

  $display .= wp_nonce_field( "wpqpa_attachment", "wpqpa_nonce", true, false );
  $display .= wpqpa_file_attachment_list( $post );  
  $display .= '</div>';
  echo $display;
}

function wpqpa_file_attachment_list( $post ){
  global $wpdb;
  $display = '<div class="wpqpa-files-list" >';

  $sql  = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpqpa_post_attachments WHERE post_id = %d order by updated_at desc ", $post->ID );
  $files_list = $wpdb->get_results( $sql );

  foreach( $files_list as $file_row ){
    $url = get_permalink( $file_row->post_id );
    $url = wpqpa_add_query_string( $url, "wpqpa_file_download=yes&wpqpa_private_file_id=".$file_row->id."&wpqpa_post_id=".$file_row->post_id );

    $display .= '
      <div class="wpqpa-file-item" id="PF'.$file_row->id.'"  data-file-id="'.$file_row->id.'" >
        <div class="wpqpa-file-item-row"    >
            <div class="wpqpa-file-item-name wpqpa-files-list-name" >'.$file_row->file_name.'</div>
            <div class="wpqpa-file-item-download" ><a href="'.$url.'" >'.__("Download","wpqpa").'</a></div>
            <div class="wpqpa-clear"></div>
        </div>
        <div class="wpqpa-clear"></div>
      </div>';    
  }
  $display .= '</div>';
  return $display;
}


add_action( 'init', 'wpqpa_save_private_attachment_files' );
function wpqpa_save_private_attachment_files(){
  global $wpdb;
  
  if( ! isset( $_POST['wpqpa_file_name'] ) ){
      return;
  }   

  $file_name    = isset( $_POST['wpqpa_file_name'] ) ? sanitize_text_field( $_POST['wpqpa_file_name'] ) : '';
  $file_nonce   = isset( $_POST['file_nonce'] ) ? ( $_POST['file_nonce'] ) : '';
  $post_id = isset( $_POST['post_ID'] ) ? (int) ( $_POST['post_ID'] ) : 0;
  $user_id    = get_current_user_id();     

  if ( isset( $_POST['wpqpa_nonce'] ) && wp_verify_nonce( $_POST['wpqpa_nonce'], 'wpqpa_attachment' ) ) {
    $result_upload = wpqpa_process_file_upload();
    if( isset( $result_upload['status'] ) && $result_upload['status'] == 'success' ){
      $file_date = date("Y-m-d H:i:s");    
      $uploaded_file_name = $result_upload['base_name'];

      $wpqpa_post_attachments_table = "{$wpdb->prefix}wpqpa_post_attachments";
      $wpdb->insert( 
              $wpqpa_post_attachments_table, 
              array( 
                  'file_name'         => $file_name,
                  'user_id'           => $user_id,
                  'post_id'           => $post_id,
                  'file_path'         => $result_upload['relative_file_path'],
                  'updated_at'        => $file_date,                        
                  'uploaded_file_name' => $uploaded_file_name,
              ), 
              array( 
                  '%s',
                  '%d',
                  '%d',
                  '%s',
                  '%s',
                  '%s'
              ) ); 
    }else{
      // Handle file upload errors
    }
  }else{
    // Handle error for invalid data submission
  }
}

function wpqpa_process_file_upload( ) {
  $allowed_mime_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
  $allowed_exts = array('pdf','doc','docx');
  $max_size             = 2 * 1024 * 1024; // 2MB
  $errors = '';

  if ( isset( $_FILES ) ) {
    foreach ( $_FILES as $key => $array ) {            
      extract( $array );
      if ( $name ) {
        $clean_file = true;                               
        preg_match( "/.(".implode("|",$allowed_exts).")$/i",$name, $extstatus_matches );
        
        if ( ! in_array( $type, $allowed_mime_types ) ) {
          $errors = __('File extension is not allowed. Please choose a different file.','wpqpa').'<br/>';
        } elseif ( $size > $max_size ) {
          $errors = __('File size exceeded.', 'wpqpa').'<br/>';
        } elseif ( count( $extstatus_matches ) == 0 ) {                    
          $errors = __('File extension is not allowed. Please choose a different file.', 'wpqpa').'<br/>';
        } else {
          if ( ! ( $upload_dir = wp_upload_dir() ) )
            $upload_dir =  false;

          if ( $upload_dir ) { 
			$folder_path = "/wpqpa/";
            $target_path = $upload_dir['basedir'] . $folder_path;

            if ( ! is_dir( $target_path ) )
                mkdir( $target_path, 0777 );
            
            
            $base_name = sanitize_file_name( wp_basename( $name ) );
            $base_name = preg_replace('/\.(?=.*\.)/', '_', $base_name );
            
            $time_val = time();
            $target_path = $target_path .   $time_val . '_' . $base_name;
            $nice_url = $upload_dir['baseurl'] . $folder_path;
            $relative_file_path = $folder_path. $time_val . '_' . $base_name;
            $nice_url = $nice_url . $time_val . '_' . $base_name;
            move_uploaded_file( $tmp_name, $target_path );                            
           
            return array('status' => 'success', 'relative_file_path' => $relative_file_path, 
                'file_path' => $nice_url, 'msg' => __('File uploaded successfully.','wpqpa'),
                'base_name' => preg_replace('/\.(?=.*\.)/', '_', wp_basename( $name ) ) );
          }                    
        }
      }else{
        $errors = __('Please select a file to upload.','wpqpa');
      }
    }
  }
  return array('status' => 'error', 'msg' => $errors);
}

add_action( 'post_edit_form_tag' , 'wpqpa_post_edit_form_tag' );
function wpqpa_post_edit_form_tag( ) {
  echo ' enctype="multipart/form-data" ';
}

function wpqpa_add_query_string( $link, $query_str ) {
  $build_url = $link;
  $query_comp = explode( '&', $query_str );

  foreach ( $query_comp as $param ) {
    $params = explode( '=', $param );
    $key = isset( $params[0] ) ? $params[0] : '';
    $value = isset( $params[1] ) ? $params[1] : '';
    $build_url = esc_url_raw( add_query_arg( $key, $value, $build_url ) );
  }

  return $build_url;
}
    
add_action( 'init', 'wpqpa_file_attachment_download' );
function wpqpa_file_attachment_download(){
  global $wpdb;       

  if( isset( $_GET['wpqpa_file_download'] ) && sanitize_text_field($_GET['wpqpa_file_download']) =='yes'
    && isset( $_GET['wpqpa_private_file_id'] ) ){
    $wpqpa_file_download = $_GET['wpqpa_file_download'];
    $wpqpa_file_id = isset( $_GET['wpqpa_private_file_id'] ) ? (int) $_GET['wpqpa_private_file_id'] : '';
    $wpqpa_post_id = isset( $_GET['wpqpa_post_id'] ) ? (int) $_GET['wpqpa_post_id'] : '';

    if( $wpqpa_file_id != '' && $wpqpa_post_id != '' ){
      $sql  = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpqpa_post_attachments WHERE id = %d 
          AND post_id = %d order by updated_at desc ", $wpqpa_file_id, $wpqpa_post_id );
      $attachments = $wpdb->get_results( $sql,ARRAY_A );
      if( ! isset( $attachments[0] ) ){
          return;
      }

      $file_link = site_url() . $attachments[0]['file_path'];
      $upload_dir = wp_upload_dir(); 
      $file_dir =  $upload_dir['basedir'] . $attachments[0]['file_path'];           
        
      
      $file_mime_type = mime_content_type( $file_dir );
      if( $file_mime_type != '' ){
        header( 'Cache-Control: public' );
        header( 'Content-Description: File Transfer' );

        if( isset( $attachments[0]['uploaded_file_name'] ) && $attachments[0]['uploaded_file_name'] != '' ){
            header( 'Content-disposition: attachment;filename='.$attachments[0]['uploaded_file_name'] );
        }else{
            header( 'Content-disposition: attachment;filename='.basename( $file_dir ) );
        }
    
        header( 'Content-Type: '. $file_mime_type );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Content-Length: '. filesize( $file_dir ) );
        readfile( $file_dir);
        exit;
      } 
    }           
  }
}
    
add_filter( 'the_content', 'wpqpa_file_attachment_display' );
function wpqpa_file_attachment_display( $content ){
    global $post;
    if( is_singular( 'post' ) ){
        return $content . wpqpa_file_attachment_list( $post );
    }
    return $content;
}