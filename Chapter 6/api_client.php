<?php

function wpquick_rest_api_client( $url, $post_data = '' ){
$api_route = $url;
$ch = curl_init( $api_route );
$headers = array(
'Authorization:Basic YWRtaW46dTh1OHU4dTg='// <---
);
curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
if( $post_data != '' ){
curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data );
}
curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
$return = curl_exec( $ch );
echo "<pre>";
print_r( $return );
exit;
}
$post_data = array();
$post_data = json_encode( $post_data );
$api_route ="http://www.example.com/wp-json/wqra/v1/list_post_attachments/5";
wpquick_rest_api_client( $api_route, $post_data );

?>