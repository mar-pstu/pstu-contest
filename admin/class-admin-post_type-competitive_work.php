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


	protected $fields;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->post_type_name = 'competitive_work';
		$this->fields = [
			new Field( 'rating', __( 'Рейтинг', $this->plugin_name ) ),
			new Field( 'cipher', __( 'Шифр', $this->plugin_name ) ),
			new Field( 'work_files', __( 'Конкурсные работы', $this->plugin_name ) ),
			new Field( 'show_authors', __( 'Показывать авторов', $this->plugin_name ) ),
			new Field( 'authors', __( 'Авторы', $this->plugin_name ) ),
			new Field( 'reviews', __( 'Рецензии', $this->plugin_name ) ),
			new Field( 'invite_files', __( 'Приглашение к участию в конференции', $this->plugin_name ) ),
		];
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
	public function save_post( $post_id, $post ) {
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
		foreach ( $this->fields as $field ) {
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
		foreach ( $this->fields as $field ) {
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
			include dirname( __FILE__ ) . '/partials/form-group.php';
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
		$columns = array_merge( [ 'authors' => __( 'Авторы', $this->plugin_name ) ], $columns );
		$columns = array_merge( [ 'rating' => __( 'Рейтинг', $this->plugin_name ) ], $columns );
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
			case 'authors':
				$meta = get_post_meta( get_the_ID(), $column, true );
				if ( ( bool ) get_post_meta( get_the_ID(), 'show_authors', true ) ) {
					echo '<span class="dashicons-before dashicons-visibility show_authors--true">' . __( 'Видимы', $this->plugin_name ) . '</span>';
				} else {
					echo '<span class="dashicons-before dashicons-hidden show_authors--false">' . __( 'Скрыты', $this->plugin_name ) . '</span>';
				}
				if ( is_array( $meta ) && ! empty( $meta ) ) {
					echo '<ul class="' . $column . '">';
					array_map( function ( $item ) {
						echo ( is_array( $item ) && ! empty( $item ) ) ? '<li>' . implode( ' ', array_values( $item ) ) . '</li>' : '';
					} , $meta );
					echo '</ul>';
				}
				break;
			case 'rating':
			case 'cipher':
				$meta = get_post_meta( get_the_ID(), $column, true );
				if ( ! empty( trim( $meta ) ) ) {
					echo '<span class="' . $column . '">' . $meta . '</span>';
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
		$sortable_columns[ 'rating' ] = array( 'rating', false );
		return $sortable_columns;
	}


	/**
	 * Изменяем запрос для возможности сортировки по пользовательским
	 * колонкам таблицы постов
	 * @since    1.0.0
	 * @param    WP_Query  $object   объект запроса
	 */
	public function request_custom_sortable_columns( $object ) {
		if ( in_array( $object->get( 'orderby' ), [ 'cipher', 'rating' ] ) ) {
			$object->set( 'meta_key', $object->get( 'orderby' ) );
			$object->set( 'orderby', 'meta_value_num' );
		}
	}


	/**
	 * Добавляем поле для поиска по метаполю "Шифр"
	 * на страницу списка постов
	 * @param  string               $post_type  типо поста
	 * @param  WP_Media_List_Table  $which      расположение дополнительной табличной навигационной разметки
	 */
	public function add_search_field_by_meta( $post_type, $which ) {
		if ( $this->post_type_name == $post_type ) {
			echo $this->render_input( 'ciphe', 'text', [
				'placeholder' => __( 'Поиск по шифру', $this->plugin_name ),
				'value'       => ( isset( $_GET[ 'ciphe' ] ) ) ? esc_attr( $_GET[ 'ciphe' ] ) : '',
			] );
		}
	}


	/**
	 * Фильтр, который изменяем параметры запроса и добавляет поиск по метаполю "Шифр"
	 * @param    array  $query_vars  параметры запроса, которые нужно изменить 
	 * @return   array  $query_vars  параметры запроса, которые нужно изменить 
	 */
	public function search_request_by_meta( $query_vars ) {
		global $pagenow;
		global $post_type;
		if ( 'edit.php' == $pagenow && $this->post_type_name == $post_type ) {
			if ( isset( $_GET[ 'ciphe' ] ) && ! empty( $_GET[ 'ciphe' ] ) ) {
				$query_vars[ 'meta_query' ] = [
					'relation' => 'OR',
					[
						'key'     => 'cipher',
						'value'   => ( string ) sanitize_text_field( $_GET[ 'ciphe' ] ),
						'type'    => 'char',
						'compare' => 'LIKE'
					],
				];
			}
		}
		return $query_vars;
	}



	/**
	 * Фильтр, который добавляет вкладку с опциями для текущего типа записи
	 * на страницу настроектплагина
	 * @since    2.0.0
	 * @param    array     $tabs     исходный массив вкладок идентификатор вкладки=>название
	 * @return   array     $tabs     отфильтрованный массив вкладок идентификатор вкладки=>название
	 */
	public function add_settings_tab( $tabs ) {
		$post_type = get_post_type_object( $this->post_type_name );
		if ( ! is_null( $post_type ) ) {
			$tabs[ $this->post_type_name ] = $post_type->labels->name;
		}
		return $tabs;
	}


	/**
	 * Выводит html-код формы ввода настроек для таксономии
	 * @param    string    $page_slug    идентификатор страницы настроек
	 */
	public function render_settings_form( string $page_slug ) {
		?>
			<form action="options.php" method="POST">
				<?php
					settings_fields( $this->post_type_name );
					do_settings_sections( $this->post_type_name );
					submit_button();
				?>
			</form>
		<?php
	}


	/**
	 * Регистрирует настройки для таксономии
	 * "Год проведения"
	 * @since    2.0.0
	 * @param    string    $page_slug    идентификатор страницы настроек
	 */
	public function register_settings( $page_slug ) {
		register_setting( $this->post_type_name, $this->post_type_name, [ $this, 'sanitize_setting_callback' ] );
		add_settings_section( 'table', __( 'Таблица', $this->plugin_name ), [ $this, 'render_section_info' ], $this->post_type_name ); 
		add_settings_field( 'table_style', __( 'Стиль', $this->plugin_name ), [ $this, 'render_setting_field'], $this->post_type_name, 'table', 'table_style' );
	}


	/**
	 * Описание секции настроек
	 * @param  [type] $section [description]
	 */
	public function render_section_info( $section ) {
		// справка
	}



	/**
	 * Формирует и вывоит html-код элементов формы настроек плагина
	 * для таксономии "Год проведения"
	 * @since    2.0.0
	 * @param    string    $id       идентификатор опции
	 */
	public function render_setting_field( $id ) {
		$options = get_option( $this->post_type_name );
		switch ( $id ) {
			// стиль таблицы постов
			case 'table_style':
				$value = ( isset( $options[ $id ] ) && ! empty( $options[ $id ] ) ) ? $options[ $id ] : 'default';
				$choices = [
					'default'     => __( 'Стандартный', $this->plugin_name ),
					'blue'        => 'Blue',
					'dark'        => 'Dark',
					'dropbox'     => 'Dropbox',
					'green'       => 'Green',
					'grey'        => 'Grey',
					'ice'         => 'Ice',
					'materialize' => 'Materialize',
					'metro-dark'  => 'Metro dark',
					'blackice'    => 'Black ice',
					'jui'         => 'Jui',
				];
				echo $this->render_dropdown( "{$this->post_type_name}[{$id}]", $choices, [
					'selected'          => $value,
					'id'                => '',
					'show_option_none'  => false,
					'option_none_value' => false,
				] );
				break;
		}
	}


	/**
	 * Очистка данных
	 * @since    2.0.0
	 * @var      array    $options
	 */
	public function sanitize_setting_callback( $options ) {
		$result = [];
		foreach ( $options as $name => &$value ) {
			$new_value = null;
			switch ( $name ) {
				case 'table_style':
					$value = sanitize_key( $value );
					$new_value = ( in_array( $value, [ 'blue', 'dark', 'dropbox', 'green', 'grey', 'ice', 'materialize', 'metro-dark', 'blackice', 'jui' ] ) ) ? $value : 'default';
					break;
			}
			if ( null != $new_value && ! empty( $new_value ) ) {
				$result[ $name ] = $new_value;
			}
		}
		return $result;
	}


}