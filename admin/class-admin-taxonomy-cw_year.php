<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность админки для
 * таксономии "Год проведения"
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminTaxonomyCWYear extends AdminPartTaxonomy {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->taxonomy_name = 'cw_year';
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
	 * "Год проведения"
	 * @since    2.0.0
	 * @param    string    $page_slug    идентификатор страницы настроек
	 */
	public function register_settings( $page_slug ) {
		register_setting( $this->plugin_name, $this->taxonomy_name, [ $this, 'sanitize_setting_callback' ] );
		add_settings_section( 'current', 'Текущий период', [ $this, 'render_section_info' ], $page_slug ); 
		add_settings_field( 'current_year', __( 'Текущий год', $this->plugin_name ), [ $this, 'render_setting_field'], $page_slug, 'current_year', 'current_year' );
	}


	/**
	 * Описание секции настроек
	 * @param  [type] $section [description]
	 */
	public function render_section_info( $section ) {
		// описание настройки
	}



	/**
	 * Формирует и вывоит html-код элементов формы настроек плагина
	 * для таксономии "Год проведения"
	 * @since    2.0.0
	 * @param    string    $id       идентификатор опции
	 */
	public function render_setting_field( $id ) {
		$options = get_option( $this->taxonomy_name );
		switch ( $id ) {
			// Текущий год
			case 'current_year':
				$value = ( isset( $options[ $id ] ) ) ? $options[ $id ] : [];
				// 
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
				case 'current_year':
					//
					break;
			}
			if ( null != $new_value ) {
				$result[ $name ] = $new_value;
			}
		}
		return $result;
	}


}