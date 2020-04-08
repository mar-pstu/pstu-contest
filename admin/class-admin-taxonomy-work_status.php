<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность админки для
 * таксономии "Статус работы"
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminTaxonomyWorkStatus extends AdminPartTaxonomy {


	/**
	 * Идентификатор таксономии "Статус конкурсной работы",
	 * @since    2.0.0
	 * @var      string
	 */
	protected $taxonomy_name;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->taxonomy_name = 'work_status';
	}


	/**
	 * Метод добавления пользовательского мета поля на страницу редактирования нового термина
	 * таксономии "Статус конкурсной работы"
	 * @param    WP_Term   $term     текущий объект таксономии
	 */
	public function edit_custom_fields( $term ) {
		$options = get_option( $this->taxonomy_name );
		foreach ( apply_filters( "{$this->plugin_name}_get_fields", $this->taxonomy_name ) as $field ) {
			$id = $field->get_name();
			$label = $field->get_label();
			$control = '';
			$value = get_term_meta( $term->term_id, $id, true );
			switch ( $id ) {
				case 'status_type':
					$control = $this->render_dropdown( $id, wp_list_pluck( ( isset( $options[ 'types' ] ) ) ? $options[ 'types' ] : [], 'label', 'slug' ), [ 'selected' => $value ] );
					break;
			}
			include dirname( __FILE__ ) . '\partials\taxonomy-edit-section-field.php';
		}
	}


	/**
	 * Метод добавления пользовательского мета поля на страницу добавления нового термина
	 * таксономии "Статус конкурсной работы"
	 * @param    string    $taxonomy_name     идентификатор таксономии
	 */
	public function add_custom_fields( $taxonomy_name ) {
		$options = get_option( $this->taxonomy_name );
		foreach ( apply_filters( "{$this->plugin_name}_get_fields", $this->taxonomy_name ) as $field ) {
			$id = $field->get_name();
			$label = $field->get_label();
			$control = '';
			switch ( $id ) {
				case 'status_type':
					$control = $this->render_dropdown( $id, wp_list_pluck( ( isset( $options[ 'types' ] ) ) ? $options[ 'types' ] : [], 'label', 'slug' ) );
					break;
			}
			include dirname( __FILE__ ) . '\partials\taxonomy-add-section-field.php';
		}
	}


	/**
	 * Сохрание пользовательских метаполей таксономии
	 * @param    int       $term_id  идентификатор текущего терма
	 */
	public function save_custom_fields( $term_id ) {
		if ( ! current_user_can( 'edit_term', $term_id ) ) return;
		if (
			( isset( $_POST[ '_wpnonce' ] ) && ! wp_verify_nonce( $_POST[ '_wpnonce' ], "update-tag_$term_id" ) ) ||
			( isset( $_POST[ '_wpnonce_add-tag' ] ) && ! wp_verify_nonce( $_POST[ '_wpnonce_add-tag' ], "add-tag" ) )
		) return;
		foreach ( apply_filters( "{$this->plugin_name}_get_fields", $this->taxonomy_name ) as $field ) {
			$new_value = ( isset( $_REQUEST[ $field->get_name() ] ) ) ? $this->sanitize_meta_field( $field->get_name(), $_REQUEST[ $field->get_name() ] ) : '';
			if ( empty( $new_value ) ) {
				delete_term_meta( $term_id, $field->get_name() );
			} else {
				update_term_meta( $term_id, $field->get_name(), $new_value );
			}
		}
	}


	/**
	 * Проверка полученного мета-поля перед сохранением в базу
	 * @since    2.0.0
	 * @var      string    $key      Идентификатор поля
	 * @var      string    $value    Новое значение металополя
	 */
	protected function sanitize_meta_field( $key, $value ) {
		switch ( $key ) {
			default:
				$result = sanitize_text_field( $value );
				break;
		}
		return $result;
	}


	/**
	 * Фильтр вкладок на странице настроек
	 * @since    2.0.0
	 * @param    array     $tabs     исходный массив вкладок идентификатор вкладки=>название
	 * @return   array     $tabs     отфильтрованный массив вкладок идентификатор вкладки=>название
	 */
	public function add_settings_tab( $tabs ) {
		global $wp_taxonomies;
		if ( isset( $wp_taxonomies[ $this->taxonomy_name ] ) ) {
			$tabs[ $this->taxonomy_name ] = $wp_taxonomies[ $this->taxonomy_name ]->labels->name;
		}
		return $tabs;
	}


	/**
	 * Регистрирует настройки для таксономии
	 * "Статус конкурсной работы"
	 * @since    2.0.0
	 * @param    string    $page_slug    идентификатор страницы настроек
	 */
	public function register_settings( $page_slug ) {
		register_setting( $this->plugin_name, $this->taxonomy_name, [ $this, 'sanitize_setting_callback' ] );
		add_settings_section( 'types', 'Типы', function ( $args ) {
			// echo "<pre>";
			// var_dump( $args );
			// echo "</pre>";
		}, $page_slug ); 
		add_settings_field( 'types', __( 'Типы статутов', $this->plugin_name ), [ $this, 'render_setting_field'], $page_slug, 'types', 'types' );
	}


	/**
	 * Формирует и вывоит html-код элементов формы настроек плагина
	 * для таксономии "Статус конкурсной работы"
	 * @since    2.0.0
	 * @param    string    $id       идентификатор опции
	 */
	public function render_setting_field( $id ) {
		$options = get_option( $this->taxonomy_name );
		switch ( $id ) {
			// статусы работ
			case 'types':
				$value = ( isset( $options[ $id ] ) ) ? $options[ $id ] : [];
				echo $this->render_list_of_templates( "{$this->taxonomy_name}_{$id}", $value, [
					'template' => $this->render_composite_field(
						$this->render_input( "{$this->taxonomy_name}[{$id}][{{data.i}}][slug]", 'text', [
							'value'    => '{{data.value.slug}}',
							'class'    => 'form-control',
							'id'       => '',
							'placeholder' => __( 'Идентификатор', $this->plugin_name ),
						] ),
						$this->render_input( "{$this->taxonomy_name}[{$id}][{{data.i}}][label]", 'text', [
							'value'    => '{{data.value.label}}',
							'class'    => 'form-control',
							'id'       => '',
							'placeholder' => __( 'Название', $this->plugin_name ),
						] ),
						$this->render_input( "{$this->taxonomy_name}[{$id}][{{data.i}}][text_color]", 'text', [
							'value'    => '{{data.value.color}}',
							'class'    => 'form-control data-picker-control',
							'id'       => '',
							'placeholder' => __( 'Цвет текста', $this->plugin_name ),
						] ),
						$this->render_input( "{$this->taxonomy_name}[{$id}][{{data.i}}][bg_color]", 'text', [
							'value'    => '{{data.value.color}}',
							'class'    => 'form-control data-picker-control',
							'id'       => '',
							'placeholder' => __( 'Цвет фона', $this->plugin_name ),
						] )
					),
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
				case 'types':
					if ( is_array( $value ) ) {
						$new_value = [];
						foreach ( $value as &$item ) {
							$item = $this->parse_only_allowed_args(
								[ 'slug'  => '', 'label' => '', 'text_color' => '#000', 'bg_color' => '#fff' ],
								$item,
								[ function ( $slug ) { return preg_replace( '/[^a-z\d]/ui', '', $slug ); }, 'sanitize_text_field', 'sanitize_hex_color', 'sanitize_hex_color' ],
								[ 'slug', 'label', 'text_color', 'bg_color' ]
							);
							if ( null !== $item ) {
								$new_value[] = $item;
							}
						}
					}
					break;
			}
			if ( null != $new_value ) {
				$result[ $name ] = $new_value;
			}
		}
		return $result;
	}



	/**
	 * Регистрирует стили для админки
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		parent::enqueue_styles();
		wp_enqueue_style( 'wp-color-picker' );
	}


	/**
	 * Регистрирует скрипты для админки
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();
		wp_enqueue_script( 'wp-color-picker' );
	}



}