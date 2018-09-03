<?php
add_action( 'wp_enqueue_scripts', 'wpquickstart_enqueue_styles' );
function wpquickstart_enqueue_styles() {
    wp_enqueue_style( 'twenty-seventeen-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'wpquickstart-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('twenty-seventeen-style')
    );
}

add_filter( 'template_include', 'wpquickstart_conditional_template', 99 );
function wpquickstart_conditional_template( $template ) {
	if ( is_page( 'portfolio' ) ) {
		$new_template = locate_template( array( 'portfolio-page-template.php' ) );
		if ( !empty( $new_template ) ) { 
			return $new_template;
		}
	}
	return $template;
}