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
class AdminSettings extends Part {


	use Controls;


	protected $settings;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->settings = [
			'wokr_status' => [
				new Field( 'status_types', __( 'Тип статуса', $this->plugin_name ) ),
			],
		];
	}


	/**
	 * Создаем страницу настроек плагина
	 */
	public function add_page() {
		add_submenu_page(
			'edit.php?post_type=competitive_work',
			__( 'Настройки плагина "Конкурс "Энергетика"', $this->plugin_name ),
			__( 'Настройки', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			[ $this, 'render_page' ]
		);
	}


	/**
	 * Генерируем html код страницы настроек
	 */
	public function render_page() {
		$tabs = [
			'wokr_status' => __( 'Статусы работ', $this->plugin_name ),
			'import' => __( 'Импорт', $this->plugin_name ),
			'export' => __( 'Экспорт', $this->plugin_name ),
		];
		$current_tab = ( isset( $_REQUEST[ 'tab' ] ) && in_array( $_REQUEST[ 'tab' ], array_keys( $tabs ) ) ) ? $_REQUEST[ 'tab' ] : array_keys( $tabs )[ 0 ];
		$page_title = get_admin_page_title();
		$page_content = $this->render_nav_tab_wrapper( $tabs, $current_tab );
		switch ( $current_tab ) {
			case 'wokr_status':
				ob_start();
					?>
						<form action="options.php" method="POST">
							<?php
								settings_fields( $current_tab );
								do_settings_sections( $current_tab );
								submit_button();
							?>
						</form>
					<?php
				$page_content .= ob_get_contents();
				ob_end_clean();
				break;
			case 'import':
				$page_content .= 'импорт';
				break;
			case 'export':
				$page_content .= 'експорт';
				break;
			default:
				# code...
				break;
		}
		include dirname( __FILE__ ) . '\partials\admin-page.php';
	}



	/**
	 *	Генериреут вкладки
	 */
	protected function render_nav_tab_wrapper( array $tabs, $current_tab = '' ) {
		$result = [];
		if ( ! empty( $tabs ) ) {
			foreach ( $tabs as $slug => $label ) {
				$result[] = sprintf(
					'<a href="edit.php?post_type=competitive_work&page=%1$s&tab=%2$s" class="nav-tab %3$s">%4$s</a>',
					$this->plugin_name,
					$slug,
					( $slug == $current_tab ) ? 'nav-tab-active' : '',
					$label
				);
			}
		}
		return '<nav class="nav-tab-wrapper wp-clearfix">' . implode( "\r\n", $result ) . '</nav>';
	}


	function register_settings() {
		foreach ( $this->settings as $option => &$fields ) {
			register_setting( $option, $option, [ $this, 'sanitize_callback_' . $option ] );
			add_settings_section( "section_{$option}", '', '', $option );
			foreach ( $fields as $field ) {
				add_settings_field( $field->get_name(), $field->get_label(), [ $this, 'render_setting_field' ], $option, "section_{$option}", [ $option => $field->get_name() ] );
			}
		}		
	}


	function render_setting_field( $args ) {
		$option_name = key( $args );
		$field_name = current( $args );
		$options = get_option( $option_name );
		switch ( $option_name ) {

			// статусы работ
			case 'wokr_status':
				switch ( $field_name ) {
					case 'status_types':
						$value = ( isset( $options[ $field_name ] ) ) ? $options[ $field_name ] : array();
						echo $this->render_list_of_templates( "{$option_name}_{$field_name}", $value, [
							'template' => $this->render_composite_field(
								$this->render_input( "{$option_name}[{$field_name}][{{data.i}}][slug]", 'text', [
									'value'    => '{{data.value.slug}}',
									'class'    => 'form-control',
									'id'       => '',
									'placeholder' => __( 'Идентификатор', $this->plugin_name ),
								] ),
								$this->render_input( "{$option_name}[{$field_name}][{{data.i}}][label]", 'text', [
									'value'    => '{{data.value.label}}',
									'class'    => 'form-control',
									'id'       => '',
									'placeholder' => __( 'Название', $this->plugin_name ),
								] ),
								$this->render_input( "{$option_name}[{$field_name}][{{data.i}}][color]", 'text', [
									'value'    => '{{data.value.color}}',
									'class'    => 'form-control data-picker-control',
									'id'       => '',
									'placeholder' => __( 'Цвет', $this->plugin_name ),
								] )
							),
						] );
						break;
					
					default:
						# code...
						break;
				}
				break;
			
			default:
				# code...
				break;
		}
	}


	protected function var_dump( $var ) {
		echo "<pre>";
		var_dump( $var );
		echo "</pre>";
	}



	/**
	 * Очистка данных
	 *
	 * @var  array    $options
	 */
	function sanitize_callback_wokr_status( $options ) {
		$result = '';
		foreach ( $options as $name => &$value ) {

			switch ( $name ) {

				case 'status_types':
					$result = array_map( function ( $item ) {
						if ( ! is_array( $item ) ) {
							$item = [];
						}
						return array(
							'slug'  => ( isset( $item[ 'slug' ] ) ) ? preg_replace( '/[^ a-z\d]/ui', '', $item[ 'slug' ] ) : '',
							'label' => ( isset( $item[ 'label' ] ) ) ? sanitize_text_field( $item[ 'label' ] ) : '',
							'color' => ( isset( $item[ 'color' ] ) ) ? sanitize_hex_color( $item[ 'color' ] ) : '#fff',
						);
					}, $value );
					// $this->var_dump( $result );
					break;

			}
		}
		return $result;
	}



	/**
	 * Регистрирует стили для админки
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'wp-color-picker' );
	}


	/**
	 * Регистрирует скрипты для админки
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery', 'wp-util', 'wp-color-picker' ), $this->version, false );
	}



}