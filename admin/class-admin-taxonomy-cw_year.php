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
	 * Регистрирует настройки для таксономии
	 * "Год проведения"
	 * @since    2.0.0
	 * @param    string    $page_slug    идентификатор страницы настроек
	 */
	public function register_settings( $page_slug ) {
		register_setting( $this->taxonomy_name, $this->taxonomy_name, [ $this, 'sanitize_setting_callback' ] );
		add_settings_section( 'current', __( 'Текущий год', $this->plugin_name ), [ $this, 'render_section_info' ], $this->taxonomy_name ); 
		add_settings_field( 'current_year', __( 'Текущий год', $this->plugin_name ), [ $this, 'render_setting_field'], $this->taxonomy_name, 'current', 'current_year' );
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
		$options = get_option( $this->taxonomy_name );
		switch ( $id ) {
			// Текущий год
			case 'current_year':
				$value = ( isset( $options[ $id ] ) ) ? $options[ $id ] : [];
				$choices = get_terms( [
					'taxonomy'   => $this->taxonomy_name,
					'hide_empty' => false,
					'fields'     => 'id=>name',
				] );
				if ( is_array( $choices ) && ! empty( $choices ) ) {
					echo $this->render_dropdown( "{$this->taxonomy_name}[{$id}]", $choices, [ 'selected' => $value, 'id' => '' ] );
				} else {
					_e( 'Таксономия не заполнена', $this->plugin_name );
				}
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
					$value = sanitize_key( $value );
					if ( ! empty( $value ) ) {
						$new_value = $value;
					}
					break;
			}
			if ( null != $new_value ) {
				$result[ $name ] = $new_value;
			}
		}
		return $result;
	}


}