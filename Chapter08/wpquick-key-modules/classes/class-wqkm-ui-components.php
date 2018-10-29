<?php

class WQKM_UI_Components {
    
  public function __construct(){
	add_action('admin_menu', array( $this , 'add_settings_page') );   

    add_action( 'wp_ajax_wqkm_save_slider_images', array( $this, 'save_slider_images') );
    add_shortcode( 'wqkm_product_slider', array( $this, 'product_slider') );    
    
    $this->post_type                = 'wqkm_accordion';
    add_action( 'init', array( $this, 'create_accordion_post_type' ) );
    add_action( 'add_meta_boxes', array( $this, 'add_accordion_meta_boxes' ) );
    add_action( 'save_post', array( $this, 'save_accordion_meta_data' ) );
    add_shortcode( 'wqkm_accordion', array( $this, 'display_accordion') );  
  }
  
  public function add_settings_page(){
	add_menu_page( __('UI Component Settings', 'wqkm' ), __('UI Component Settings', 'wqkm' ),
          'manage_options','wqkm-settings', array( &$this,'ui_settings' ) ); 
  }

  public function ui_settings(){
      global $wqkm,$wqkm_template_data;        
      
      $slider_images = (array) get_option('wqkm_slider_images');

      $upload_dir = wp_upload_dir();
      $display_images = '';
      foreach ($slider_images as $slider_image) {
          if( $slider_image != '' )
              $display_images .= '<img src="' . $upload_dir['baseurl'] . $slider_image . '" style="width:100px;height:100px" />';
      }
      $wqkm_template_data['display_images'] = $display_images;

      ob_start();
      $wqkm->template_loader->get_template_part( 'ui-settings');
      $display = ob_get_clean();
      echo $display;
  }

  public function save_slider_images(){
    global $wpdb;
    
    $file_nonce   = isset( $_POST['file_nonce'] ) ? ( $_POST['file_nonce'] ) : '';
    $user_id    = get_current_user_id();     

    if(check_ajax_referer( 'wqkm-private-admin', 'file_nonce',false )){ 
      $result_upload = $this->process_file_upload();

      if( isset( $result_upload['status'] ) && $result_upload['status'] == 'success' ){
        $file_date = date("Y-m-d H:i:s");    
        $uploaded_file_name = $result_upload['base_name'];

        $slider_images = (array) get_option('wqkm_slider_images');
        $slider_images[] = $result_upload['relative_file_path'];
        update_option( 'wqkm_slider_images', $slider_images );

        $upload_dir = wp_upload_dir();
        $display_images = '';
        foreach ($slider_images as $slider_image) {
          if( $slider_image != '' )
              $display_images .= '<img src="' . $upload_dir['baseurl'] . $slider_image . '" style="width:100px;height:100px" />';
        }
        $result = array( 'status' => 'success', 'msg' => $result_upload['msg'] , 'images' => $display_images );

      }else{
        $result = array( 'status' => 'error', 'msg' => $result_upload['msg'] );
      }
    }else{
      $result = array( 'status' => 'error', 'msg' => __('Invalid file upload request.','wqkm') );
    }

    echo json_encode($result);exit;
  }

  public function process_file_upload( ) {
    $allowed_mime_types = array( 'image/png', 'image/jpg', 'image/jpeg');
    $allowed_exts = array( 'jpg','png','jpeg');
    $max_size             = 2 * 1024 * 1024; // 2MB
    $errors = '';

    if ( isset( $_FILES ) ) { 
      foreach ( $_FILES as $key => $array ) {            
        extract( $array );

        if ( $name ) {
          $clean_file = true;                               
          preg_match( "/.(".implode("|",$allowed_exts).")$/i",$name, $extstatus_matches );
          
          if ( ! in_array( $type, $allowed_mime_types ) ) {
            $errors = __('File extension is not allowed. Please choose a different file.','wqkm').'<br/>';
          } elseif ( $size > $max_size ) {
            $errors = __('File size exceeded.', 'wqkm').'<br/>';
          } elseif ( count( $extstatus_matches ) == 0 ) {                    
            $errors = __('File extension is not allowed. Please choose a different file.', 'wqkm').'<br/>';
          } else {
            if ( ! ( $upload_dir = wp_upload_dir() ) )
              $upload_dir =  false;

            if ( $upload_dir ) { 
              $folder_path = "/wqkm/";
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
                  'file_path' => $nice_url, 'msg' => __('File uploaded successfully.','wqkm'),
                  'base_name' => preg_replace('/\.(?=.*\.)/', '_', wp_basename( $name ) ) );
            }                    
          }
        }else{
          $errors = __( 'Please select a file to upload.', 'wqkm' );
        }
      }
    }
    return array( 'status' => 'error', 'msg' => $errors );
  }

  public function product_slider( $atts, $content ){
      $sh_attr = shortcode_atts( array(
          'width' => '520',
          'height' => '320',
      ), $atts );

      wp_enqueue_style( 'wqkm-slider' );
      wp_enqueue_style( 'wqkm-slider-demo' );        
      wp_enqueue_script( 'wqkm-slider' );
      wp_enqueue_script( 'wqkm-front' );

      $custom_js_strings = array(        
          'width' => $sh_attr['width'],
          'height' => $sh_attr['height']
      );
      wp_localize_script( 'wqkm-front', 'WQKMFront', $custom_js_strings );

      $slider_images = (array) get_option('wqkm_slider_images');
      $upload_dir = wp_upload_dir();          

      $display = '<div id="banner-fade">  
                      <ul class="bjqs">';
      foreach ($slider_images as $slider_image) {
          if( $slider_image != '' )
              $display .= '<li><img src="' . $upload_dir['baseurl'] . $slider_image . '"  /></li>';
      }
                          
      $display .= '   </ul>
                  <div>';

      return $display;
  }    

  public function create_accordion_post_type() {
      global $wqkm;
      
      $post_type = $this->post_type;
      $singular_post_name = __( 'Accordion','wqkm' );
      $plural_post_name = __( 'Accordions','wqkm' );

      $labels = array(
          'name'                  => sprintf( __( '%s', 'wqkm' ), $plural_post_name),
          'singular_name'         => sprintf( __( '%s', 'wqkm' ), $singular_post_name),
          'add_new'               => __( 'Add New', 'wqkm' ),
          'add_new_item'          => sprintf( __( 'Add New %s ', 'wqkm' ), $singular_post_name),
          'edit_item'             => sprintf( __( 'Edit %s ', 'wqkm' ), $singular_post_name),
          'new_item'              => sprintf( __( 'New  %s ', 'wqkm' ), $singular_post_name),
          'all_items'             => sprintf( __( 'All  %s ', 'wqkm' ), $plural_post_name),
          'view_item'             => sprintf( __( 'View  %s ', 'wqkm' ), $singular_post_name),
          'search_items'          => sprintf( __( 'Search  %s ', 'wqkm' ), $plural_post_name),
          'not_found'             => sprintf( __( 'No  %s found', 'wqkm' ), $plural_post_name),
          'not_found_in_trash'    => sprintf( __( 'No  %s  found in the Trash', 'wqkm' ), $plural_post_name),
          'parent_item_colon'     => '',
          'menu_name'             => sprintf( __( '%s', 'wqkm' ), $plural_post_name),
      );

      $args = array(
          'labels'                => $labels,
          'hierarchical'          => true,
          'description'           => __( 'Accordion Description', 'wqkm' ),
          'supports'              => array( 'title'),
          'public'                => false,
          'show_ui'               => true,
          'show_in_menu'          => true,
          'show_in_nav_menus'     => true,
          'publicly_queryable'    => false,
          'exclude_from_search'   => false,
          'has_archive'           => true,
          'query_var'             => true,
          'can_export'            => true,
          'rewrite'               => true
      );

      register_post_type( $post_type, $args );
  }

  public function add_accordion_meta_boxes() {
      add_meta_box( 'wqkm-accordion-meta', __('Accordion Details','wqkm'), array( $this, 'display_accordion_meta_boxes' ), $this->post_type );
  }

  public function display_accordion_meta_boxes( $accordion ) {
      global $wqkm,$template_data;

      $template_data['accordion_post_type']      = $this->post_type;
      $template_data['accordion_nonce']     = wp_create_nonce('wqkm-accordion-meta');
      $template_data['wqkm_tab_1'] = get_post_meta( $accordion->ID, '_wqkm_tab_1', true );
      $template_data['wqkm_tab_2'] = get_post_meta( $accordion->ID, '_wqkm_tab_2', true );
      $template_data['wqkm_tab_3'] = get_post_meta( $accordion->ID, '_wqkm_tab_3', true );

      ob_start();
      $wqkm->template_loader->get_template_part( 'accordion','meta');
      $display = ob_get_clean();
      echo $display;
  }

  public function save_accordion_meta_data( $post_id ) {
      global $post,$wqkm;

      if ( !wp_verify_nonce($_POST['accordion_nonce'], 'wqkm-accordion-meta' ) ) {
           return $post_id;
      }

      if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
          return $post_id;
      }    

      if ( $this->post_type == $_POST['post_type'] && current_user_can( 'edit_post', $post->ID ) ) {
          $wqkm_tab_1  = isset( $_POST['wqkm_tab_1'] ) ? wp_kses_post( trim($_POST['wqkm_tab_1']) ) : '';
          $wqkm_tab_2  = isset( $_POST['wqkm_tab_2'] ) ? wp_kses_post( trim($_POST['wqkm_tab_2']) ) : '';
          $wqkm_tab_3  = isset( $_POST['wqkm_tab_3'] ) ? wp_kses_post( trim($_POST['wqkm_tab_3']) ) : '';
          
          update_post_meta( $post_id, '_wqkm_tab_1', $wqkm_tab_1 );
          update_post_meta( $post_id, '_wqkm_tab_2', $wqkm_tab_2 );
          update_post_meta( $post_id, '_wqkm_tab_3', $wqkm_tab_3 );
          
      } else {
          return $post_id;
      }
  }

  public function display_accordion( $atts, $content ){
      $sh_attr = shortcode_atts( array(
          'id' => '0',
      ), $atts );
      extract($sh_attr);

      wp_enqueue_style('wqkm-jquery-ui-style');
      wp_enqueue_script('wqkm-accordion');

      $display = '<div id="accordion">';

      if( trim( get_post_meta( $id, '_wqkm_tab_1' , true ) ) != '' ){ 
        $display .= '<h3>'.get_the_title( $id ).'</h3>';
        $display .= '<div>
                      <p>'.get_post_meta( $id, '_wqkm_tab_1' , true ).'
                      </p>
                    </div>';
      }

      if( trim( get_post_meta( $id, '_wqkm_tab_2' , true ) ) != '' ){
        $display .= '<h3>'.get_the_title( $id ).'</h3>';
        $display .= '<div>
                      <p>'.get_post_meta( $id, '_wqkm_tab_2' , true ).'
                      </p>
                    </div>';
      }

      if( trim( get_post_meta( $id, '_wqkm_tab_3' , true ) ) != '' ){
        $display .= '<h3>'.get_the_title( $id ).'</h3>';
        $display .= '<div>
                      <p>'.get_post_meta( $id, '_wqkm_tab_3' , true ).'
                      </p>
                    </div>';
      }


      $display .= '</div>';


      return $display;
  }
}
?>
