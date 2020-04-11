<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс отвечающий за функциональность публичной части
 * сайта для пользовательских таксономий конкурсных работ
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class PublicPartTaxonomy extends PublicPart {


	/**
	 * Идентификатор пользовательской таксономии
	 * @since    2.0.0
	 * @var      string
	 */
	protected $taxonomy_name;


	/**
	 * Возвращает идентификатор пользовательской таксономии
	 * @return   string   идентификатор пользовательской таксономии
	 */
	public function get_taxonomy_name() {
		return $this->taxonomy_name;
	}


	/**
	 * Выбирает шиблон для вывода контента
	 * @param  string $original_template шаблон для подключения
	 * @return string                    шаблон для подключения
	 */
	function select_template_include( string $original_template  ) {
		$template = $original_template;
		if ( is_tax( $this->taxonomy_name ) ) {
			$template = dirname( __FILE__ ) . '/partials/archive-template.php';
		}
		return $template;
	}


}