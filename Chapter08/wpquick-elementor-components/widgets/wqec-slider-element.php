<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Widget_WQEC_Slider extends Widget_Base {

	public function get_name() {
		return 'wqec-slider';
	}

	public function get_title() {
		return __( 'Product Image Slider', 'wqec' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'general-elements' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_wqec_slider',
			[
				'label' => __( 'Product Image Slider', 'wqec' ),
			]
		);

		$this->add_control(
			'wqec_images_list',
			[
				'label' => __( 'Add Images', 'wqec' ),
				'type' => Controls_Manager::GALLERY,
			]
		);		

		$this->add_control(
			'wqec_slider_width',
			[
				'label' => __( 'Slider Width', 'wqec' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter Slider Width', 'wqec' ),
				'default' => '',
				'label_block' => true
			]
		);

		$this->add_control(
			'wqec_slider_height',
			[
				'label' => __( 'Slider Height', 'wqec' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'Enter Slider Height', 'wqec' ),
				'default' => '',
				'label_block' => true
			]
		);		

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings();

		if ( ! $settings['wqec_images_list'] ) {
			return;
		}

		$id_int = substr( $this->get_id_int(), 0, 3 );
		$this->add_render_attribute( 'shortcode', 'id_int', $id_int );

		$ids = wp_list_pluck( $settings['wqec_images_list'], 'id' );
		$this->add_render_attribute( 'shortcode', 'slider_images', implode( ',', $ids ) );

		if ( isset($settings['wqec_slider_width']) &&  $settings['wqec_slider_width'] != '' ) {
			$this->add_render_attribute( 'shortcode', 'slider_width', $settings['wqec_slider_width'] );
		}
		if ( isset($settings['wqec_slider_height'] ) && $settings['wqec_slider_height'] != '') {
			$this->add_render_attribute( 'shortcode', 'slider_height', $settings['wqec_slider_height'] );
		}		
		
		?>
		<div class="wqec-image-slider">
			<?php 
			echo do_shortcode( '[wqec_image_slider ' . $this->get_render_attribute_string( 'shortcode' ) . ']' );
			?>
		</div>
		<?php
	}
}

Plugin::instance()->widgets_manager->register_widget_type( new Widget_WQEC_Slider() );