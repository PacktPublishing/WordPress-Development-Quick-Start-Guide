<?php
/*
  Plugin Name: WQ REST API
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-rest-api
  Description: Add REST API features
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */

//add_filter( 'rest_endpoints', 'wpquick_rest_endpoints');
function wpquick_rest_endpoints( $endpoints ){
    if ( isset( $endpoints['/wp/v2/posts'] ) ) {
        unset( $endpoints['/wp/v2/posts'] );
    }

    return $endpoints;
}

function wqra_routes() {

    register_rest_route( 'wqra/v1', '/read_posts', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'wqra_read_posts_handler',
        'permission_callback' => function () {
    			return true;
    		}
    ) );

    register_rest_route( 'wqra/v1', '/list_post_attachments/(?P<id>\d+)', array(
	    'methods' => 'POST',
	    'callback' => 'wqra_list_post_attachments_handler',
	    'args' => array(
	      'id' => array(
	        'validate_callback' => function($param, $request, $key) {
	          return is_numeric($param);
	        }
	      ),
	    ),
	 ) ); 
}

add_action( 'rest_api_init', 'wqra_routes' );


function wqra_read_posts_handler() {
	$posts_query = new WP_Query(array('post_type' => 'post','post_status' =>'publish',
                  'order' => 'desc', 'orderby' => 'date', 'category__not_in' => array( 20 ),'posts_per_page'=>-1 ));

	$data = array();
	if($posts_query->have_posts()){
	    while($posts_query->have_posts()) : $posts_query->the_post();	    	
	        array_push($data, array("ID" => get_the_ID(), "title" => get_the_title() ));
	    endwhile;
	}

    return rest_ensure_response(($data));
}


function wqra_list_post_attachments_handler($data){
	global $wpdb;

	$post_data = json_decode($data->get_body());
	$data = $data->get_params();	
	$post_id = isset($data['id']) ? $data['id'] : 0;

  $post_attachments_table = $wpdb->prefix.'wpqpa_post_attachments'; 
  $sql  = $wpdb->prepare( "SELECT * from $post_attachments_table where post_id = %d ", $post_id);

  $result = $wpdb->get_results($sql);

  $post_attachments = array();
  if($result){
      foreach ($result as $key => $value) {
      	$post_attachments[] = array('ID'=> $value->id, 'file_name' => $value->file_name, 
      		'file_path' => $value->file_path);
      }
  }

  return rest_ensure_response(($post_attachments));
} 