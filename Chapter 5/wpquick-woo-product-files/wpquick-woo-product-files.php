<?php
/*
  Plugin Name: WQWPF Product Files
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-woocommerce-product-files
  Description: Add and display files for WooCommerce products
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */

register_activation_hook( __FILE__, 'wqwpf_activate' );
function wqwpf_activate(){
  global $wpdb,$wp_roles;

  $table_product_files = $wpdb->prefix . 'wqwpf_product_files';
  $sql_product_files = "CREATE TABLE IF NOT EXISTS $table_product_files (
      id int(11) NOT NULL AUTO_INCREMENT,
      user_id int(11) NOT NULL,
      post_id int(11) NOT NULL,
      file_path longtext NOT NULL,
      updated_at datetime NOT NULL,
      uploaded_file_name varchar(255) NOT NULL,
      PRIMARY KEY (id)
    );";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql_product_files );
}


add_action( 'plugins_loaded', 'wqwpf_plugins_loaded_action' );
function wqwpf_plugins_loaded_action() {
  if( class_exists('WooCommerce')){
    add_action( 'admin_enqueue_scripts', 'wqwpf_admin_load_scripts',9 );
    add_action( 'wp_enqueue_scripts', 'wqwpf_load_scripts',9 );
    add_filter( 'woocommerce_product_data_tabs', 'wqwpf_custom_product_tabs' );
    add_action( 'woocommerce_product_data_panels', 'wqwpf_product_files_panel_content' );
    add_action( 'init', 'wqwpf_product_file_download' );
    add_action( 'wp_ajax_wqwpf_save_product_files', 'wqwpf_save_product_files' );
    add_filter( 'woocommerce_product_tabs', 'wqwpf_product_files_tab' );
  }
}

if ( ! defined( 'WQWPF_PLUGIN_DIR' ) ) {
  define( 'WQWPF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WQWPF_PLUGIN_URL' ) ) {
  define( 'WQWPF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


function wqwpf_admin_load_scripts(){
        
    wp_register_style( 'wqwpf_admin_css', WQWPF_PLUGIN_URL . 'css/wqwpf-admin.css' );
    wp_enqueue_style( 'wqwpf_admin_css' );
    
    wp_register_script( 'wqwpf_admin_js', WQWPF_PLUGIN_URL . 'js/wqwpf-admin.js', array('jquery') );
    wp_enqueue_script( 'wqwpf_admin_js' );
    
    $custom_js_strings = array(        
        'AdminAjax' => admin_url('admin-ajax.php'),
        'Messages'  => array(
                            'fileRequired' => __('File is required.','wqwpf'),   
                        ),
        'nonce' => wp_create_nonce('wqwpf-private-admin'),
    );

    wp_localize_script( 'wqwpf_admin_js', 'WQWPFAdmin', $custom_js_strings );
}


function wqwpf_load_scripts(){
        
    wp_register_style( 'wqwpf_front_css', WQWPF_PLUGIN_URL . 'css/wqwpf-front.css' );
    wp_enqueue_style( 'wqwpf_front_css' );
}


function wqwpf_custom_product_tabs( $tabs ) {
    $tabs['wqwpf_files'] = array(
        'label'     => __( 'Product Files', 'wqwpf' ),
        'target'    => 'wqwpf_file_options',
        'class'     => array( 'show_if_simple' ),
    );
    return $tabs;
}

    	
function wqwpf_product_files_panel_content() {
    global $post;
    ?>
    <div id='wqwpf_file_options' class='panel woocommerce_options_panel'>
        <div class='options_group'>
            <div id="wqwpf-product-files-msg"></div>
            <p class="form-field _wqwpf_product_files_field show_if_simple" style="display: block;">
        		<label for="_wqwpf_product_files"><?php _e('Product Files','wqwpf'); ?></label>
        		<input type="file" name="wqwpf_product_files" id="wqwpf_product_files" />
                <input type="hidden" id="wqwpf_product_file_nonce" name="wqwpf_product_file_nonce" />
                <input type="button" name="wqwpf_product_file_upload" id="wqwpf_product_file_upload"  class="" value="<?php echo __('Upload','wqwpf'); ?>" />
        		<span class="description"><?php echo _e('Files related to products are added to be displayed on .','wqwpf'); ?></span>
            </p>
    	</div>
        <div id="wqwpf-files-container">
        <?php echo wqwpf_product_file_list( $post->ID ); ?>
        </div>
    </div>
    <?php
}


function wqwpf_save_product_files(){
  global $wpdb;
  
  $file_nonce   = isset( $_POST['file_nonce'] ) ? ( $_POST['file_nonce'] ) : '';
  $post_id = isset( $_POST['post_id'] ) ? (int) ( $_POST['post_id'] ) : 0;
  $user_id    = get_current_user_id();     

  if(check_ajax_referer( 'wqwpf-private-admin', 'file_nonce',false )){ 
    $result_upload = wqwpf_process_file_upload();

    if( isset( $result_upload['status'] ) && $result_upload['status'] == 'success' ){
      $file_date = date("Y-m-d H:i:s");    
      $uploaded_file_name = $result_upload['base_name'];

      $wqwpf_product_files_table = "{$wpdb->prefix}wqwpf_product_files";
      $wpdb->insert( 
              $wqwpf_product_files_table, 
              array( 
                  'user_id'           => $user_id,
                  'post_id'           => $post_id,
                  'file_path'         => $result_upload['relative_file_path'],
                  'updated_at'        => $file_date,                        
                  'uploaded_file_name' => $uploaded_file_name,
              ), 
              array( '%d','%d','%s', '%s','%s' ) ); 

        $files_list = wqwpf_product_file_list( $post_id );  

        $result = array( 'status' => 'success', 'msg' => $result_upload['msg'] , 'files' => $files_list );

    }else{
      $result = array( 'status' => 'error', 'msg' => $result_upload['msg'] );
    }
  }else{
    $result = array( 'status' => 'error', 'msg' => __('Invalid file upload request.','wqwpf') );
  }

  echo json_encode($result);exit;
}

function wqwpf_process_file_upload( ) {
  $allowed_mime_types = array( 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
  $allowed_exts = array( 'pdf','doc','docx' );
  $max_size             = 2 * 1024 * 1024; // 2MB
  $errors = '';

  if ( isset( $_FILES ) ) { 
    foreach ( $_FILES as $key => $array ) {            
      extract( $array );

      if ( $name ) {
        $clean_file = true;                               
        preg_match( "/.(".implode("|",$allowed_exts).")$/i",$name, $extstatus_matches );
        
        if ( ! in_array( $type, $allowed_mime_types ) ) {
          $errors = __('File extension is not allowed. Please choose a different file.','wqwpf').'<br/>';
        } elseif ( $size > $max_size ) {
          $errors = __('File size exceeded.', 'wqwpf').'<br/>';
        } elseif ( count( $extstatus_matches ) == 0 ) {                    
          $errors = __('File extension is not allowed. Please choose a different file.', 'wqwpf').'<br/>';
        } else {
          if ( ! ( $upload_dir = wp_upload_dir() ) )
            $upload_dir =  false;

          if ( $upload_dir ) { 
            $folder_path = "/wqwpf/";
            $target_path = $upload_dir['basedir'] . $folder_path;

            if ( ! is_dir( $target_path ) )
                mkdir( $target_path, 0777 );
            
            
            $base_name = sanitize_file_name( wp_basename( $name ) );
            $base_name = preg_replace('/\.(?=.*\.)/', '_', $base_name );
            
            $time_val = time();
            $target_path = $target_path .  $time_val . '_' . $base_name;
            $nice_url = $upload_dir['baseurl'] . $folder_path;
            $relative_file_path = $folder_path. $time_val . '_' . $base_name;
            $nice_url = $nice_url . $time_val . '_' . $base_name;
            move_uploaded_file( $tmp_name, $target_path );                            
           
            return array( 'status' => 'success', 'relative_file_path' => $relative_file_path, 
                'file_path' => $nice_url, 'msg' => __('File uploaded successfully.','wqwpf'),
                'base_name' => preg_replace('/\.(?=.*\.)/', '_', wp_basename( $name ) ) );
          }                    
        }
      }else{
        $errors = __( 'Please select a file to upload.', 'wqwpf' );
      }
    }
  }
  return array( 'status' => 'error', 'msg' => $errors );
}

function wqwpf_product_file_list($post_id){
    global $wpdb;

    $sql  = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wqwpf_product_files WHERE post_id = %d order by updated_at desc ", $post_id );
    $files_list = $wpdb->get_results( $sql );
      
    $display = '<div class="wqwpf-files-list" >';
    foreach( $files_list as $file_row ){
        $url = get_permalink( $file_row->post_id );
        $url = wqwpf_add_query_string( $url, "wqwpf_file_download=yes&wqwpf_private_file_id=".$file_row->id."&wqwpf_post_id=".$file_row->post_id );
        
        $file_display = '
          <div class="wqwpf-file-item" id="PF'.$file_row->id.'"  data-file-id="'.$file_row->id.'" >
            <div class="wqwpf-file-item-row"    >
                <div class="wqwpf-file-item-name wqwpf-files-list-name" >'.$file_row->uploaded_file_name.'</div>
                <div class="wqwpf-file-item-download" ><a href="'.$url.'" >'.__("Download","wqwpf").'</a></div>
                <div class="wqwpf-clear"></div>
            </div>
            <div class="wqwpf-clear"></div>
          </div>';  
          $file_display = apply_filters( 'wqwpf_post_attachment_list_item', $file_display, $file_row ); 

          $display .= $file_display; 

    }
    $display .= '</div>';
    return $display;
}

function wqwpf_add_query_string( $link, $query_str ) {
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


function wqwpf_product_file_download(){
  global $wpdb;       

  if( isset( $_GET['wqwpf_file_download'] ) && sanitize_text_field($_GET['wqwpf_file_download']) =='yes'
    && isset( $_GET['wqwpf_private_file_id'] ) ){
    $wqwpf_file_download = $_GET['wqwpf_file_download'];
    $wqwpf_file_id = isset( $_GET['wqwpf_private_file_id'] ) ? (int) $_GET['wqwpf_private_file_id'] : '';
    $wqwpf_post_id = isset( $_GET['wqwpf_post_id'] ) ? (int) $_GET['wqwpf_post_id'] : '';

    if( $wqwpf_file_id != '' && $wqwpf_post_id != '' ){
      $sql  = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wqwpf_product_files WHERE id = %d 
          AND post_id = %d order by updated_at desc ", $wqwpf_file_id, $wqwpf_post_id );
      $attachments = $wpdb->get_results( $sql,ARRAY_A );
      if( ! isset( $attachments[0] ) ){
          return;
      }

      $file_link = site_url() . $attachments[0]['file_path'];
      $upload_dir = wp_upload_dir(); 
      $file_dir =  $upload_dir['basedir'] . $attachments[0]['file_path'];           
        
      
      $file_mime_type = mime_content_type( $file_dir );
      if( $file_mime_type != '' ){

        do_action( 'wqwpf_before_download_post_attachment', $attachments[0] );

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



function wqwpf_product_files_tab( $tabs ) {
    $tabs['wqwpf_tab'] = array(
        'title'     => __( 'Product Files', 'wqwpf' ),
        'priority'  => 50,
        'callback'  => 'wqwpf_product_files_tab_content'
    );

    return $tabs;
}

function wqwpf_product_files_tab_content() {
    global $post;
    echo wqwpf_product_file_list( $post->ID );
}