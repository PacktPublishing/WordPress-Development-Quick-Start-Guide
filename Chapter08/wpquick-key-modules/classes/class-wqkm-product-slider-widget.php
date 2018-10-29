<?php

class WQKM_Product_Slider_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'wqkm_product_slider', 
			esc_html__( 'Product Slider', 'wqkm' ), 
			array( 'description' => esc_html__( 'Main product slider', 'wqkm' ), ) 
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];		
		echo do_shortcode('[wqkm_product_slider width="'.$instance['width'].'" height="'.$instance['height'].'"  /]');
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$width = ! empty( $instance['width'] ) ? (int) $instance['width'] : 640;
		$height = ! empty( $instance['height'] ) ? (int) $instance['height'] : 320;
		?>
		<p>
		<label><?php esc_attr_e( 'Width:', 'wqkm' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'width' ) ); ?>" type="text" value="<?php echo esc_attr( $width ); ?>">
		</p>
		<p>
		<label><?php esc_attr_e( 'Height:', 'wqkm' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'height' ) ); ?>" type="text" value="<?php echo esc_attr( $height ); ?>">
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['width'] = ( ! empty( $new_instance['width'] ) ) ? (int) $new_instance['width'] : '';
		$instance['height'] = ( ! empty( $new_instance['height'] ) ) ? (int) $new_instance['height'] : '';
		return $instance;
	}

}