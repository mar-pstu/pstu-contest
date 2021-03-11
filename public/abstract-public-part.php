<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс "частей" публичной части
 * плагина
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class PublicPart extends Part {

	/**
	 * Регистрирует стили для админки
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/public.css', array(), $this->version, 'all' );
	}


	/**
	 * Регистрирует скрипты для админки
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/public.min.js',  array( 'jquery' ), $this->version, false );
	}


}