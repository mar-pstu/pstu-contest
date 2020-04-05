<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность админки для
 * типа записи "Конкурсная работа"
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminCompetitiveWork extends Part {


	use Controls;


	/**
	 *	Регистрация метабокса
	 *
	 * @since    2.0.0
	 * @var      string       $post_type
	 */
	public function add_meta_box( $post_type ) {
		if ( 'competitive_work' == $post_type ) {
			add_meta_box(
				'competitive_work_meta',
				__( 'Параметры', $this->plugin_name ),
				array( $this, 'render_metabox_content' ),
				$post_type,
				'advanced',
				'high',
				null
			);
		}
	}



	/**
	 * СОхранение поста
	 *
	 * @since    2.0.0
	 * @var      int          $post_id
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST[ 'competitive_work_nonce' ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ 'competitive_work_nonce' ], 'competitive_work' ) ) { wp_nonce_ays(); return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( 'page' == $_POST[ 'post_type' ] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_nonce_ays();
			return;
		}
		foreach ( apply_filters( "{$this->plugin_name}_get_fields", 'competitive_work' ) as $field ) {
			$new_value = ( isset( $_REQUEST[ $field->get_name() ] ) ) ? $this->sanitize_field( $field->get_name(), $_REQUEST[ $field->get_name() ] ) : '';
			if ( empty( $new_value ) ) {
				delete_post_meta( $post_id, $field->get_name() );
			} else {
				update_post_meta( $post_id, $field->get_name(), $new_value );
			}
		}
	}


	/**
	 * Проверка полученного поля перед сохранением в базу
	 *
	 * @since    2.0.0
	 * @var      string    $key      Идентификатор поля
	 * @var      string    $value    Новое значение металополя
	 */
	protected function sanitize_field( $key, $value ) {
		switch ( $key ) {
			case 'authors':
				$result = array();
				// $this->var_dump( $value );
				if ( is_array( $value ) && ! empty( $value ) ) {
					foreach ( $value as &$author ) {
						if ( is_array( $author ) && ! empty( $author ) ) {
							$author = array_merge( [
								'last_name'   => '',
								'first_name'  => '',
								'middle_name' => '',
							], $author );
							// $result[] = $this->array_map_assoc( 'sanitize_text_field', $author );
							$result[] = $author;
						}
					}
				}
				break;
			case 'invite_files':
			case 'reviews':
			case 'work_files':
				$result = array();
				if ( is_array( $value ) ) {
					foreach ( $value as $url ) {
						if ( ( bool ) preg_match( '~^(?:https?://)?[^.]+\.\S{2,4}$~iu', $url ) ) {
							$result[] = trim( $url );
						}
					}
				}
				break;
			case 'show_authors':
				$result = ( bool ) $value;
				break;
			case 'rating':
			case 'cipher':
			default:
				$result = sanitize_text_field( $value );
				break;
		}
		return $result;
	}


	public function array_map_assoc( $function, $array ){
		$result = array();
		foreach( $array as $key => $value ){
			$function( $key, $value );
			$result[ $key ] = $val;
		}
		return $result;
	}


	/**
	 * Регистрирует стили для админки
	 *
	 * @since    2.0.0
	 * @var      WP_Post       $post
	 */
	public function render_metabox_content( $post ) {
		wp_nonce_field( 'competitive_work', 'competitive_work_nonce' );
		wp_enqueue_media();
		foreach ( apply_filters( "{$this->plugin_name}_get_fields", 'competitive_work' ) as $field ) {
			$label = $field->get_label();
			$name = $field->get_name();
			$id = $field->get_name();
			$value = get_post_meta( $post->ID, $field->get_name(), true );
			switch ( $field->get_name() ) {
				case 'show_authors':
					$args = [ 'id' => $id ];
					if ( ! empty( $value ) ) $args[ 'checked' ] = 'checked';
					$control = $this->render_checkbox( $name, $value, '', $args );
					break;
				case 'authors':
					$control = $this->render_list_of_templates( $name, $value, [
						'template' => $this->render_composite_field(
							$this->render_input( $name . '[{{data.i}}][last_name]', 'text', [
								'value'    => '{{data.value.last_name}}',
								'class'    => 'form-control',
								'id'       => '',
								'placeholder' => __( 'Фамилия', $this->plugin_name ),
							] ),
							$this->render_input( $name . '[{{data.i}}][first_name]', 'text', [
								'value'    => '{{data.value.first_name}}',
								'class'    => 'form-control',
								'id'       => '',
								'placeholder' => __( 'Имя', $this->plugin_name ),
							] ),
							$this->render_input( $name . '[{{data.i}}][middle_name]', 'text', [
								'value'    => '{{data.value.middle_name}}',
								'class'    => 'form-control',
								'id'       => '',
								'placeholder' => __( 'Отчество', $this->plugin_name ),
							] )
						)
					] );
					break;
				case 'invite_files':
				case 'reviews':
				case 'work_files':
					$control = $this->render_list_of_templates( $name, $value, array(
						'template' => $this->render_file_choice( $name . '[{{data.i}}]', 'text', [
							'value'    => '{{data.value}}',
							'class'    => 'form-control',
							'id'       => '',
						] ) )
					);
					break;
				case 'rating':
				case 'cipher':
				default:
					$control = $this->render_input( $name, 'text', [
						'value'    => $value,
						'class'    => 'form-control',
						'id'       => $id,
					] );
					break;
			}
			include dirname( __FILE__ ) . '\partials\post_type-section-field.php';
		}
	}



	/**
	 * Регистрирует стили для админки
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
	}


	/**
	 * Регистрирует скрипты для админки
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery', 'wp-util' ), $this->version, false );
	}



}