<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за страницу импорта
 * конкурсных работ
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminImport extends AdminPart {


	use Controls;


	/**
	 * Создаем страницу с формой испорта
	 */
	public function add_page() {
		add_submenu_page(
			'edit.php?post_type=competitive_work',
			__( 'Импорт конкурсных работ', $this->plugin_name ),
			__( 'Импорт', $this->plugin_name ),
			'manage_options',
			$this->plugin_name . '_import',
			[ $this, 'render_page' ]
		);
	}


	/**
	 * Генерируем html код страницы настроек
	 */
	public function render_page() {
		$page_title = get_admin_page_title();
		$page_content = '';
		include dirname( __FILE__ ) . '/partials/admin-page.php';
	}


}