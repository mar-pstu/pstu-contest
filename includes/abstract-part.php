<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс "частей" плагина
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class Part {

	/**
	 * Имя плагина и слаг метаполей
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    Уникальный идентификтор плагина в контексте WP
	 */
	protected $plugin_name;

	/**
	 * Версия плагина
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    Номер текущей версии плагина
	 */
	protected $version;


	/**
	 * Инициализация класса и установка его свойства.
	 *
	 * @since    2.0.0
	 * @param    string    $plugin_name       Имя плагин и слаг метаполей
	 * @param    string    $version           Текущая версия
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	protected function var_dump( $var ) {
		echo "<pre>";
		var_dump( $var );
		echo "</pre>";
	}



}