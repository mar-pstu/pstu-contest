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
class Init {


	/**
	 * Уникальный идентификатор для получения строки перевода.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $version    Уникальный идентификатор для получения строки перевода.
	 */
	protected $plugin_name;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param    string    $plugin_name        Уникальный идентификатор для получения строки перевода.
	 */
	function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;
	}


	/**
	 * Регистрирует типы записей
	 *
	 * @since    2.0.0
	 */
	public function register_post_types() {
		$this->type_competitive_work();
	}


	/**
	 * Регистрирует таксономии
	 *
	 * @since    2.0.0
	 */
	public function register_taxonomies() {
		$this->taxonomy_contest_section();
		$this->taxonomy_cw_year();
		$this->taxonomy_work_status();
	}


	/**
	 * Регистрирует тип записи "Конкурсная работа"
	 *
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
			'taxonomies'          => array( 'post_tag', 'category', 'contest_section', 'work_status', 'cw_year' ),
			'has_archive'         => true,
			'rewrite'             => true,
			'query_var'           => true,
		) );
	}


	/**
	 * Регистрирует таксономию "Секция конкурса"
	 *
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
	 * Регистрирует таксономию "Секция конкурса"
	 *
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


	protected function taxonomy_work_status() {
		register_taxonomy( 'work_status', [ 'competitive_work' ], [
			'label'                 => 'work_status',
			'labels'                => [
				'name'                => __( 'Статус работы', $this->plugin_name ),
				'singular_name'       => __( 'Статус роботи', $this->plugin_name ),
				'search_items'        => __( 'Знайти статус роботи', $this->plugin_name ),
				'all_items'           => __( 'Всі записи', $this->plugin_name ),
				'view_item '          => __( 'Перегляд списку записів', $this->plugin_name ),
				'parent_item'         => __( 'Батьківський запис', $this->plugin_name ),
				'parent_item_colon'   => __( 'Батьківський запис:', $this->plugin_name ),
				'edit_item'           => __( 'Редагувати запис', $this->plugin_name ),
				'update_item'         => __( 'Оновити запис', $this->plugin_name ),
				'add_new_item'        => __( 'Додати новий запис', $this->plugin_name ),
				'new_item_name'       => __( 'Додати запис', $this->plugin_name ),
				'menu_name'           => __( 'Статус роботи', $this->plugin_name ),
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


}