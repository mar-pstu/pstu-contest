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
class AdminCompetitiveWork extends AdminPartPostType {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->post_type_name = 'competitive_work';
	}


	/**
	 *	Регистрация метабокса
	 * @since    2.0.0
	 * @var      string       $post_type
	 */
	public function add_meta_box( $post_type ) {
		if ( $post_type == $this->post_type_name ) {
			add_meta_box(
				$this->post_type_name . '_meta',
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
	 * Сохранение записи типа "конкурсная работа"
	 * @since    2.0.0
	 * @var      int          $post_id
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST[ "{$this->post_type_name}_nonce" ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ "{$this->post_type_name}_nonce" ], $this->post_type_name ) ) { wp_nonce_ays(); return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( 'page' == $_POST[ 'post_type' ] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_nonce_ays();
			return;
		}
		foreach ( apply_filters( "{$this->plugin_name}_get_fields", $this->post_type_name ) as $field ) {
			$new_value = ( isset( $_REQUEST[ $field->get_name() ] ) ) ? $this->sanitize_meta_field( $field->get_name(), $_REQUEST[ $field->get_name() ] ) : '';
			if ( empty( $new_value ) ) {
				delete_post_meta( $post_id, $field->get_name() );
			} else {
				update_post_meta( $post_id, $field->get_name(), $new_value );
			}
		}
	}


	/**
	 * Проверка полученного поля перед сохранением в базу
	 * @since    2.0.0
	 * @var      string    $key      Идентификатор поля
	 * @var      string    $value    Новое значение металополя
	 */
	protected function sanitize_meta_field( $key, $value ) {
		switch ( $key ) {
			case 'authors':
				$result = $this->sanitize_person_data( $value );
				break;
			case 'invite_files':
			case 'reviews':
			case 'work_files':
				$result = $this->sanitize_url_list( $value );
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


	/**
	 * Регистрирует стили для админки
	 * @since    2.0.0
	 * @var      WP_Post       $post
	 */
	public function render_metabox_content( $post ) {
		wp_nonce_field( $this->post_type_name, "{$this->post_type_name}_nonce" );
		wp_enqueue_media();
		foreach ( apply_filters( "{$this->plugin_name}_get_fields", $this->post_type_name ) as $field ) {
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
	 * Регистрирует скрипты для админки
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();
		wp_enqueue_media();
	}


	/**
	 * Добавляет новую колонку в таблицу записей
	 * @since    1.0.0
	 * @param    array     $columns  массив идентификаторов и заголовков колонок
	 */
	public function add_custom_columns( $columns ) {
		$columns = array_merge( [ 'cipher' => __( 'Шифр', $this->plugin_name ) ], $columns );
		return $columns;
	}


	/**
	 * Выводит содержимое ячейки таблицы записей
	 * @since    1.0.0
	 * @param    string    $column   идентификатор коолонки
	 */
	public function render_custom_columns( $column ) {
		switch ( $column ) {
			case 'cipher':
				$cipher = get_post_meta( get_the_ID(), 'cipher', true );
				if ( ! empty( trim( $cipher ) ) ) {
					echo '<span class="cipher">' . $cipher . '</span>';
				}
				break;
		}
	}


	/**
	 * Добавляет сортировку к пользовательской колонке таблицы постов
	 * @since    1.0.0
	 * @param    array     $sortable_columns     массив идентификаторов сортируемых колонок
	 */
	public function add_custom_sortable_columns( $sortable_columns ) {
		$sortable_columns[ 'cipher' ] = array( 'cipher', false ); // false = asc. desc - по умолчанию
		return $sortable_columns;
	}


	/**
	 * Изменяем запрос для возможности сортировки по пользовательским
	 * колонкам таблицы постов
	 * @since    1.0.0
	 * @param    WP_Query  $object   объект запроса
	 */
	public function request_custom_sortable_columns( $object ) {
		if( 'ciphe' == $object->get( 'orderby' ) ) {
			$object->set( 'meta_key', 'cipher' );
			$object->set( 'orderby', 'meta_value_num' );
		}
	}


}