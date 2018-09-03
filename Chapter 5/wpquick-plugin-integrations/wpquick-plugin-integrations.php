<?php
/*
  Plugin Name: WPQPI Plugin Integrations
  Plugin URI: http://www.wpexpertdeveloper.com/wpquick-plugin-integrations
  Description: Add features for uploading atatchments to posts and download from frontend
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */
  
// Exit if accessed directly
if( !defined( "ABSPATH" ) ) exit;

if ( ! defined( 'WPQPI_PLUGIN_DIR' ) ) {
  define( 'WPQPI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPQPI_PLUGIN_URL' ) ) {
  define( 'WPQPI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


add_filter( 'mycred_setup_hooks', 'wpqpi_woocommerce_hooks', 10, 2 );
function wpqpi_woocommerce_hooks( $installed, $point_type ) {
 	$installed['wpqpi_woo_purchase'] = array(
		'title'        => __( 'Points for WooCommerce Purchases', 'wpqpi' ),
		'description'  => __( 'User will get points for completing product purchases.', 'wpqpi' ),
		'callback'     => array( 'WPQPI_WooCommerce_Hooks' )
	);

	return $installed;
}

add_action( 'mycred_load_hooks', 'wpqpi_load_custom_taxonomy_hook', 10 );
function wpqpi_load_custom_taxonomy_hook() {

	class WPQPI_WooCommerce_Hooks extends myCRED_Hook {

		public function __construct( $hook_prefs, $type ) {
			parent::__construct( array(
				'id'       => 'wpqpi_woo_purchase',
				'defaults' => array(
					'creds'   => 1,
					'log'     => '%plural% for purchasing a product'
				)
			), $hook_prefs, $type );
		}

		public function run() { 
			add_action( 'woocommerce_order_status_completed', array( $this, 'wpqpi_payment_complete') );
		}

		public function wpqpi_payment_complete( $order_id ) { 
			// Check if user is excluded (required)
			if ( $this->core->exclude_user( $user_id ) ) return;

			$order = wc_get_order( $order_id );
			$total = $order->get_total();
			$credits = (int) $total / 10;
			$user = $order->get_user();
			$user_id = $user->ID;

			// Execute
			$this->core->add_creds(
				'wpqpi_woo_purchasing',
				$user_id,
				$credits,
				$this->prefs['log'],
				0,
				'',
				$m
			);

			$balance = mycred_get_users_balance( $user_id );
			if($balance > 100){
				groups_join_group(1, $user_id);
			}
		}
	}
}
