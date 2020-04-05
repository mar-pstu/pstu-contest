<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность админки для
 * таксономии "Секция работы"
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminContestSection extends Part {


	use Controls;


	protected $taxonomy_name;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->taxonomy_name = 'contest_section';
	}



	/**
	 *	Регистрация метабокса
	 *
	 * @since    2.0.0
	 * @var      string       $post_type
	 */
	public function add_meta_box( $post_type ) {
		if ( 'competitive_work' == $post_type ) {
			add_meta_box(
				$this->taxonomy_name . '_relationship',
				__( 'Секция конкурсной работы', $this->plugin_name ),
				array( $this, 'render_metabox_content' ),
				$post_type,
				'side',
				'high',
				null
			);
		}
	}



	/**
	 * Сохранение поста
	 *
	 * @since    2.0.0
	 * @var      int          $post_id
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST[ "{$this->taxonomy_name}_nonce" ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ "{$this->taxonomy_name}_nonce" ], $this->taxonomy_name ) ) { wp_nonce_ays(); return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( 'page' == $_POST[ 'post_type' ] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_nonce_ays();
			return;
		}
		$terms = array();
		if ( isset( $_POST[ $this->taxonomy_name ] ) ) {
			$terms = wp_parse_id_list( $_POST[ $this->taxonomy_name ] );
		}
		wp_set_object_terms( $post_id, $terms, $this->taxonomy_name, false );
	}


	/**
	 * Регистрирует стили для админки
	 *
	 * @since    2.0.0
	 * @var      WP_Post       $post
	 */
	public function render_metabox_content( $post ) {
		wp_nonce_field( $this->taxonomy_name, "{$this->taxonomy_name}_nonce" );
		$id = $this->taxonomy_name;
		$cw_years = get_terms( array(
			'taxonomy'   => $this->taxonomy_name,
			'hide_empty' => false,
			'fields'     => 'id=>name',
		) );
		$label = '';
		if ( is_array( $cw_years ) && ! empty( $cw_years ) ) {
			$control = $this->render_dropdown( $this->taxonomy_name, $cw_years, array(
				'class'    => 'form-control',
				'id'       => $id,
				'selected' => wp_get_object_terms( $post->ID, $this->taxonomy_name, array( 'fields' => 'ids' ) ),
			) );
		} else {
			$control = __( 'Заполните статусы работ или обратитесь к администратору сайта.', $this->plugin_name );
		}
		include dirname( __FILE__ ) . '\partials\post_type-section-field.php';
	}



}