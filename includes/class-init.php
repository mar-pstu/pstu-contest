<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Регистрирует произвольные типы записи и ппроизвольные таксономии
 *
 * @since      2.0.0
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class Init extends Part {


	/**
	 * Регистрирует типы записей
	 * @since    2.0.0
	 */
	public function register_post_types() {
		$this->type_competitive_work();
	}


	/**
	 * Регистрирует таксономии
	 * @since    2.0.0
	 */
	public function register_taxonomies() {
		$this->taxonomy_contest_section();
		$this->taxonomy_cw_year();
		$this->taxonomy_work_status();
		$this->taxonomy_university();
	}


	/**
	 * Регистрирует тип записи "Конкурсная работа"
	 * @since    2.0.0
	 */
	protected function type_competitive_work() {
		register_post_type( 'competitive_work', array(
			'label'  => null,
			'labels' => array(
				'name'               => __( 'Конкурсные работы', $this->plugin_name ),
				'singular_name'      => __( 'Конкурсная работа', $this->plugin_name ),
				'add_new'            => __( 'Добавить запись', $this->plugin_name ),
				'add_new_item'       => __( 'Добавить новую конкурсную работу', $this->plugin_name ),
				'edit_item'          => __( 'Редактировать запись', $this->plugin_name ),
				'new_item'           => __( 'Новая конкурсная работа', $this->plugin_name ),
				'view_item'          => __( 'Смотреть запись', $this->plugin_name ),
				'search_items'       => __( 'Искать запись', $this->plugin_name ),
				'not_found'          => __( 'Не найдено', $this->plugin_name ),
				'not_found_in_trash' => __( 'В корзине записни не найдены', $this->plugin_name ),
				'parent_item_colon'  => '',
				'menu_name'          => __( 'Конкурсные работы', $this->plugin_name ),
			),
			'description'         => '',
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => null,
			'show_in_rest'        => false,
			'rest_base'           => null,
			'menu_position'       => '6.67',
			'menu_icon'           => 'dashicons-awards',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'exerpt', 'comments', 'excerpt' ),
			'taxonomies'          => array( 'post_tag', 'category', 'contest_section', 'work_status', 'cw_year', 'university' ),
			'has_archive'         => true,
			'rewrite'             => true,
			'query_var'           => true,
		) );
	}


	/**
	 * Регистрирует таксономию "Секция конкурса"
	 * @since    2.0.0
	 */
	protected function taxonomy_contest_section() {
		register_taxonomy( 'contest_section', [ 'competitive_work' ], [ 
			'label'                 => '',
			'labels'                => [
				'name'              => __( 'Секции', $this->plugin_name ),
				'singular_name'     => __( 'Секция' , $this->plugin_name),
				'search_items'      => __( 'Найти запись' , $this->plugin_name),
				'all_items'         => __( 'Смотреть все запии' , $this->plugin_name),
				'view_item '        => __( 'Смотреть запись' , $this->plugin_name),
				'parent_item'       => __( 'Родительская запись', $this->plugin_name ),
				'parent_item_colon' => __( 'Родительская запись' , $this->plugin_name),
				'edit_item'         => __( 'Редактировать запись' , $this->plugin_name),
				'update_item'       => __( 'Обновить запись', $this->plugin_name ),
				'add_new_item'      => __( 'Добавить новую секцию', $this->plugin_name ),
				'new_item_name'     => __( 'Новая секция' , $this->plugin_name),
				'menu_name'         => __( 'Секции', $this->plugin_name ),
			],
			'description'           => '',
			'public'                => false,
			'publicly_queryable'    => false,
			'query_var'             => false,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'show_tagcloud'         => false,
			'show_in_rest'          => true,
			'rest_base'             => null,
			'hierarchical'          => true,
			'update_count_callback' => '',
			'rewrite'               => false,
			'capabilities'          => array(),
			'meta_box_cb'           => false,
			'show_admin_column'     => true,
			'_builtin'              => false,
			'show_in_quick_edit'    => null,
		] );
	}



	/**
	 * Регистрирует таксономию "Год проведения"
	 * @since    2.0.0
	 */
	protected function taxonomy_cw_year() {
		register_taxonomy( 'cw_year', [ 'competitive_work' ], [ 
			'label'                 => '',
			'labels'                => [
				'name'              => __( 'Годы проведения', $this->plugin_name ),
				'singular_name'     => __( 'Год проведения' , $this->plugin_name),
				'search_items'      => __( 'Найти запись' , $this->plugin_name),
				'all_items'         => __( 'Смотреть все запии' , $this->plugin_name),
				'view_item '        => __( 'Смотреть запись' , $this->plugin_name),
				'parent_item'       => __( 'Родительская запись', $this->plugin_name ),
				'parent_item_colon' => __( 'Родительская запись' , $this->plugin_name),
				'edit_item'         => __( 'Редактировать запись' , $this->plugin_name),
				'update_item'       => __( 'Обновить запись', $this->plugin_name ),
				'add_new_item'      => __( 'Добавить новый год проведения', $this->plugin_name ),
				'new_item_name'     => __( 'Новый год проведения' , $this->plugin_name),
				'menu_name'         => __( 'Годы проведения', $this->plugin_name ),
			],
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => true,
			'query_var'             => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'show_tagcloud'         => true,
			'show_in_rest'          => true,
			'rest_base'             => null,
			'hierarchical'          => true,
			'update_count_callback' => '',
			'rewrite'               => true,
			'capabilities'          => array(),
			'meta_box_cb'           => false,
			'show_admin_column'     => true,
			'_builtin'              => false,
			'show_in_quick_edit'    => null,
		] );
	}


	/**
	 * Регистрирует таксономию "Статус конкурсной работы"
	 * @since    2.0.0
	 */
	protected function taxonomy_work_status() {
		register_taxonomy( 'work_status', [ 'competitive_work' ], [
			'label'                 => 'work_status',
			'labels'                => [
				'name'                => __( 'Статусы работ', $this->plugin_name ),
				'singular_name'       => __( 'Статус работы', $this->plugin_name ),
				'search_items'        => __( 'Найти статус работы', $this->plugin_name ),
				'all_items'           => __( 'Все записи', $this->plugin_name ),
				'view_item '          => __( 'Просмотр списка записей', $this->plugin_name ),
				'parent_item'         => __( 'Родительская запись', $this->plugin_name ),
				'parent_item_colon'   => __( 'Родительская запись:', $this->plugin_name ),
				'edit_item'           => __( 'Редактировать запись', $this->plugin_name ),
				'update_item'         => __( 'Обновить запись', $this->plugin_name ),
				'add_new_item'        => __( 'Добавить новую запись', $this->plugin_name ),
				'new_item_name'       => __( 'Добавить статус', $this->plugin_name ),
				'menu_name'           => __( 'Статусы работ', $this->plugin_name ),
			],
			'description'           => '',
			'public'                => false,
			'publicly_queryable'    => false,
			'query_var'             => false,
			'show_in_nav_menus'     => false,
			'show_ui'               => true,
			'show_tagcloud'         => false,
			'show_in_rest'          => false,
			'rest_base'             => null,
			'hierarchical'          => false,
			'update_count_callback' => '',
			'rewrite'               => true,
			'capabilities'          => array(),
			'meta_box_cb'           => false,
			'show_admin_column'     => true,
			'_builtin'              => false,
			'show_in_quick_edit'    => null,
		] );
	}


	/**
	 * Регистрирует таксономию "Университет"
	 * @since    2.0.0
	 */
	protected function taxonomy_university() {
		register_taxonomy( 'university', [ 'competitive_work' ], [ 
			'label'                 => '',
			'labels'                => [
				'name'              => __( 'Университеты', $this->plugin_name ),
				'singular_name'     => __( 'Университет' , $this->plugin_name),
				'search_items'      => __( 'Найти запись' , $this->plugin_name),
				'all_items'         => __( 'Смотреть все запии' , $this->plugin_name),
				'view_item '        => __( 'Смотреть запись' , $this->plugin_name),
				'parent_item'       => __( 'Родительская запись', $this->plugin_name ),
				'parent_item_colon' => __( 'Родительская запись' , $this->plugin_name),
				'edit_item'         => __( 'Редактировать запись' , $this->plugin_name),
				'update_item'       => __( 'Обновить запись', $this->plugin_name ),
				'add_new_item'      => __( 'Добавить новую секцию', $this->plugin_name ),
				'new_item_name'     => __( 'Новая секция' , $this->plugin_name),
				'menu_name'         => __( 'Университеты', $this->plugin_name ),
			],
			'description'           => '',
			'public'                => false,
			'publicly_queryable'    => false,
			'query_var'             => false,
			'show_in_nav_menus'     => false,
			'show_ui'               => true,
			'show_tagcloud'         => false,
			'show_in_rest'          => false,
			'rest_base'             => null,
			'hierarchical'          => true,
			'update_count_callback' => '',
			'rewrite'               => true,
			'capabilities'          => array(),
			'meta_box_cb'           => false,
			'show_admin_column'     => true,
			'_builtin'              => false,
			'show_in_quick_edit'    => null,
		] );
	}


}