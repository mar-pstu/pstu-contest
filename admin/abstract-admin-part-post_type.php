<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс отвечающий за функциональность админки для
 * пользовательских типов записей
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class AdminPartPostType extends AdminPart {


	/**
	 * Идентификатор пользовательского типа записи
	 * @since    2.0.0
	 * @var      string
	 */
	protected $post_type_name;


	/**
	 * Возвращает идентификатор пользовательского типа записи
	 * @return   string   идентификатор пользовательского типа записи
	 */
	public function get_post_type_name() {
		return $this->post_type_name;
	}



}