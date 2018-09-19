<?php

class WQKM_Admin_Features{

	public function __construct(){
    add_filter( 'bulk_actions-users', array( $this, 'user_actions' ) );
    add_filter( 'handle_bulk_actions-users', array( $this, 'users_page_loaded' ), 10, 3 );
    add_action( 'admin_notices', array( $this, 'bulk_admin_notices' ) ); 


    add_filter( 'manage_users_columns', array( $this, 'manage_user_custom_columns' ) );
    add_action( 'manage_users_custom_column', array( $this, 'manage_user_custom_column_values' ), 10, 3 );
    add_filter( 'manage_users_sortable_columns', array( $this,'users_sortable_columns' ) );
    add_action( 'pre_user_query', array( $this,'users_orderby_filters' ) );
    
		add_action( 'widgets_init', array( $this, 'register_product_slider_widget' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widgets' ) );
	}

  public function user_actions($bulk_actions) {
    $bulk_actions['wpquick_featured_user'] = __('Mark Featured Profile','wqkm');
    return $bulk_actions;
  }


  public function users_page_loaded( $redirect_to, $doaction, $featured_users ) {
    if ( $doaction !== 'wpquick_featured_user' ) {
      return $redirect_to;
    }

    foreach ($featured_users as $featured_user) {
      update_user_meta($featured_user, 'wqkm_featured_status', 'ACTIVE'); 
    }

    $redirect_to = add_query_arg( 'bulk_featured_users', count( $featured_users ), $redirect_to );
    return $redirect_to;
  }


 
  public function bulk_admin_notices() {
    if ( ! empty( $_REQUEST['bulk_featured_users'] ) ) {
      $user_count = intval( $_REQUEST['bulk_featured_users'] );
      printf( '<div id="message" class="updated fade">' .
        _n( '%s Users added to featured list.',
          '%s Users added to featured list.',
          $user_count,
          'wpquick_featured_user'
        ) . '</div>', $user_count );
    }
  }

  public function manage_user_custom_columns( $column ) {
      $column['featured_user_status'] = __('Featured Status','wqkm');
      return $column;
  }

  public function manage_user_custom_column_values( $val, $column_name, $user_id ) {
      global $wqkm;
      $featured_user_status = get_user_meta( $user_id , 'wqkm_featured_status', TRUE);

      $featured_user_status = ( $featured_user_status == 'ACTIVE') ? __('ACTIVE','wqkm') : __('INACTIVE','wqkm');
      switch ($column_name) {
          case 'featured_user_status' :
              return $featured_user_status;
              break;

          default:
              return $val;
              break;
      }
  }

  public function users_sortable_columns( $columns ) {
      $columns['featured_user_status'] = 'featured_user_status';
      return $columns;
  }

  public function users_orderby_filters($userquery){ 
      global $wpdb;
      if( 'featured_user_status'==$userquery->query_vars['orderby'] ) {

          $userquery->query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wpusermeta ON ($wpdb->users.ID = wpusermeta.user_id) ";
          $userquery->query_where .= " AND wpusermeta.meta_key = 'wqkm_featured_status' ";
          $userquery->query_orderby = " ORDER BY wpusermeta.meta_value ".($userquery->query_vars["order"] == "ASC" ? "asc " : "desc ");
      
      }
  }

  public function register_product_slider_widget() {
    register_widget( 'WQKM_Product_Slider_Widget' );
  }

  public function dashboard_widgets() {
		global $wp_meta_boxes; 
		wp_add_dashboard_widget( 'wqkm_post_attachments', __('Post Attachments','wqkm'), array( $this, 'post_attachments_widget' ) );
	}
	 
	public function post_attachments_widget() {
		echo do_shortcode('[wpquick_attachment_posts]');
	}
}

?>