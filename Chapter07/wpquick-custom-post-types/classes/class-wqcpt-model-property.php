<?php

class WQCPT_Model_Property {

    public $post_type;
    public $property_category_taxonomy;
    public $property_tag_taxonomy;
    public $error_message;

    public function __construct() {
        global $wqcpt;
        
        $this->post_type                = 'wqcpt_property';
        $this->property_category_taxonomy  = 'wqcpt_property_listing_type';

        add_action( 'init', array( $this, 'create_property_post_type' ) );
        add_action( 'init', array( $this, 'create_property_custom_taxonomies' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_property_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_property_meta_data' ) );

        add_shortcode( 'wqcpt_property_form', array( $this, 'display_property_form' ) );
        add_action( 'init', array( $this, 'save_property_form' ) );
        add_action( 'template_redirect', array( $this, 'property_controller' ) );
    }

    public function create_property_post_type() {
        global $wqcpt;
        
        $post_type = $this->post_type;
        $singular_post_name = __( 'Property','wqcpt' );
        $plural_post_name = __( 'Properties','wqcpt' );

        $labels = array(
            'name'                  => sprintf( __( '%s', 'wqcpt' ), $plural_post_name),
            'singular_name'         => sprintf( __( '%s', 'wqcpt' ), $singular_post_name),
            'add_new'               => __( 'Add New', 'wqcpt' ),
            'add_new_item'          => sprintf( __( 'Add New %s ', 'wqcpt' ), $singular_post_name),
            'edit_item'             => sprintf( __( 'Edit %s ', 'wqcpt' ), $singular_post_name),
            'new_item'              => sprintf( __( 'New  %s ', 'wqcpt' ), $singular_post_name),
            'all_items'             => sprintf( __( 'All  %s ', 'wqcpt' ), $plural_post_name),
            'view_item'             => sprintf( __( 'View  %s ', 'wqcpt' ), $singular_post_name),
            'search_items'          => sprintf( __( 'Search  %s ', 'wqcpt' ), $plural_post_name),
            'not_found'             => sprintf( __( 'No  %s found', 'wqcpt' ), $plural_post_name),
            'not_found_in_trash'    => sprintf( __( 'No  %s  found in the Trash', 'wqcpt' ), $plural_post_name),
            'parent_item_colon'     => '',
            'menu_name'             => sprintf( __( '%s', 'wqcpt' ), $plural_post_name),
        );

        $args = array(
            'labels'                => $labels,
            'hierarchical'          => true,
            'description'           => __( 'Property Description', 'wqcpt' ),
            'supports'              => array( 'title', 'editor' ),
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => false,
            'has_archive'           => true,
            'query_var'             => true,
            'can_export'            => true,
            'rewrite'               => true
        );

        register_post_type( $post_type, $args );
    }

    public function create_property_custom_taxonomies() {        
        global $wqcpt;
        
        $category_taxonomy    = $this->property_category_taxonomy;
        $singular_name        = __('Property Listing Type','wqcpt');
        $plural_name          = __('Property Listing Types','wqcpt');

        $capabilities = isset($capabilities) ? $capabilities : array();
        
        register_taxonomy(
                $category_taxonomy,
                $this->post_type,
                array(
                  'labels' => array(
                    'name'              => sprintf( __( '%s ', 'wqcpt' ) , $singular_name),
                    'singular_name'     => sprintf( __( '%s ', 'wqcpt' ) , $singular_name),
                    'search_items'      => sprintf( __( 'Search %s ', 'wqcpt' ) , $singular_name),
                    'all_items'         => sprintf( __( 'All %s ', 'wqcpt' ) , $singular_name),
                    'parent_item'       => sprintf( __( 'Parent %s ', 'wqcpt' ) , $singular_name),
                    'parent_item_colon' => sprintf( __( 'Parent %s :', 'wqcpt' ) , $singular_name),
                    'edit_item'         => sprintf( __( 'Edit %s ', 'wqcpt' ) , $singular_name),
                    'update_item'       => sprintf( __( 'Update %s ', 'wqcpt' ) , $singular_name),
                    'add_new_item'      => sprintf( __( 'Add New %s ', 'wqcpt' ) , $singular_name),
                    'new_item_name'     => sprintf( __( 'New %s  Name', 'wqcpt' ) ,$singular_name),
                    'menu_name'         => sprintf( __( '%s ', 'wqcpt' ) , $singular_name),
                   ),
                   'hierarchical' => true,
                   'capabilities' => $capabilities ,
                )
        );
        
    }

    public function add_property_meta_boxes() {
        add_meta_box( 'wqcpt-property-meta', __('Property Details','wqcpt'), array( $this, 'display_property_meta_boxes' ), $this->post_type );
    }

    public function display_property_meta_boxes( $property ) {
        global $wqcpt,$template_data;

        $template_data['property_post_type']      = $this->post_type;
        $template_data['property_nonce']     = wp_create_nonce('wqcpt-property-meta');
        $template_data['wqcpt_pr_type'] = get_post_meta( $property->ID, '_wqcpt_pr_type', true );
        $template_data['wqcpt_pr_city'] = get_post_meta( $property->ID, '_wqcpt_pr_city', true );

        ob_start();
        $wqcpt->template_loader->get_template_part( 'property','meta');
        $display = ob_get_clean();
        echo $display;
    }

    public function save_property_meta_data( $post_id ) {
        global $post,$wqcpt;

        // Verify the nonce value for secure form submission
        if ( isset($_POST['property_nonce']) && !wp_verify_nonce($_POST['property_nonce'], 'wqcpt-property-meta' ) ) {
             return $post_id;
        }

        // Check for the autosaving feature of WordPress
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $post_id;
        }    

        if ( isset($_POST['post_type']) && $this->post_type == $_POST['post_type'] && current_user_can( 'edit_post', $post->ID ) ) {
            $wqcpt_pr_type  = isset( $_POST['wqcpt_pr_type'] ) ? sanitize_text_field( trim($_POST['wqcpt_pr_type']) ) : '';
            $wqcpt_pr_city  = isset( $_POST['wqcpt_pr_city'] ) ? sanitize_text_field( trim($_POST['wqcpt_pr_city']) ) : '';
            
            update_post_meta( $post_id, '_wqcpt_pr_type', $wqcpt_pr_type );
            update_post_meta( $post_id, '_wqcpt_pr_city', $wqcpt_pr_city );
            
        } else {
            return $post_id;
        }
    }

public function display_property_form( $atts, $content ){
    global $wqcpt,$template_data;
    $template_data['property_nonce']     = wp_create_nonce('wqcpt-property-meta');

    ob_start();
    $wqcpt->template_loader->get_template_part( 'property','form');
    $display = ob_get_clean(); 
    return $display;
}

public function save_property_form() {
    global $post,$wqcpt;

    if( ! isset( $_POST['wqcpt_prfr_submit'] ) ){
        return;
    }

    if ( !wp_verify_nonce($_POST['property_nonce'], 'wqcpt-property-meta' ) ||
     ! current_user_can( 'edit_post' ) ) {
        // Handle error
    }

    $wqcpt_prfr_title  = isset( $_POST['wqcpt_prfr_title'] ) ? sanitize_text_field( trim($_POST['wqcpt_prfr_title']) ) : '';
    $wqcpt_prfr_content  = isset( $_POST['wqcpt_prfr_content'] ) ? wp_kses_post( trim($_POST['wqcpt_prfr_content']) ) : '';
    $wqcpt_pr_type  = isset( $_POST['wqcpt_prfr_type'] ) ? sanitize_text_field( trim($_POST['wqcpt_prfr_type']) ) : '';
    $wqcpt_pr_city  = isset( $_POST['wqcpt_prfr_city'] ) ? sanitize_text_field( trim($_POST['wqcpt_prfr_city']) ) : '';
        
    // Validations and generate errors    
    // post fields and existencce of a post

    $post_id = wp_insert_post(
        array(
            'comment_status'    =>  'closed',
            'ping_status'       =>  'closed',
            'post_author'       =>  get_current_user_id() ,
            'post_name'         =>  sanitize_title( $wqcpt_prfr_titley ),
            'post_title'        =>  $wqcpt_prfr_title,
            'post_status'       =>  'publish',
            'post_content'      =>  $wqcpt_prfr_content,
            'post_type'         =>  $this->post_type
        )
    );

    if ( !is_wp_error( $post_id )  ) {
        update_post_meta( $post_id, '_wqcpt_pr_type', $wqcpt_pr_type );
        update_post_meta( $post_id, '_wqcpt_pr_city', $wqcpt_pr_city );
    } else {
        // Handle errors
    }
}

    
public function property_controller() {
  global $wp_query,$wqcpt,$template_data;

  $wpquick_actions = isset ( $wp_query->query_vars['wpquick_property_actions'] ) ? $wp_query->query_vars['wpquick_property_actions'] : '';
  switch ( $wpquick_actions ) {
    case 'add':
        $template_data['property_nonce']     = wp_create_nonce('wqcpt-property-meta');

        ob_start();
        $wqcpt->template_loader->get_template_part( 'property','form' );
        $display = ob_get_clean(); 
        echo get_header();
        echo $display; 
        echo get_footer();
        exit;
        break;
  }
}
   
}


?>
