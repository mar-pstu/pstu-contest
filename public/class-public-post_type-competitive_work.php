<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность публичной части сайта
 * для типа записи "Конкурсная работа"
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class PublicCompetitiveWork extends PublicPartPostType {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->post_type_name = 'competitive_work';
	}


	/**
	 * Формирует html-код с информацией о конкурсной работе
	 * @param  string $content содержимое записи
	 * @return string          обработанное содержимое записи
	 */
	public function filter_single_content( string $content ) {
		if ( get_post_type( get_the_ID() ) == $this->post_type_name ) {
			ob_start();
			foreach ( [ 'rating', 'cipher', 'work_files', 'show_authors', 'authors', 'reviews', 'invite_files' ] as $meta_key ) {
				$$meta_key = get_post_meta( get_the_ID(), $meta_key, true );
			}
			$work_status = get_terms( [
				'taxonomy'    => 'work_status',
				'hide_empty'  => false,
				'object_ids'  => get_the_ID(),
				'number'      => 1,
			] );
			$work_status = ( is_array( $work_status ) && ! empty( $work_status ) ) ? $work_status[ 0 ] : null;
			$universities = get_terms( [
				'taxonomy'   => 'university',
				'hide_empty' => false,
				'fields'     => 'names',
				'object_ids' => get_the_ID(),
			] );
			include dirname( __FILE__ ) . '/partials/single-content.php';
			$content = ob_get_contents();
			ob_end_clean();
		}
		return $content;
	}


	/**
	 * Выбирает шиблон для вывода контента
	 * @param  string $original_template шаблон для подключения
	 * @return string                    шаблон для подключения
	 */
	function select_template_include( string $original_template  ) {
		$template = $original_template;
		if ( is_post_type_archive( $this->post_type_name ) ) {
			$template = dirname( __FILE__ ) . '/partials/archive-template.php';
		}
		return $template;
	}


	/**
	 * Убирает пагинацию при выводе записей
	 * @param WP_Query $query запрос
	 */
	public function set_nopaging( $query ) {
		if ( $query->is_main_query() && $query->get( 'post_type' ) == $this->post_type_name ) {
			$query->set( 'nopaging', true );
			$query->set( 'posts_per_page', -1 );
			$query->set( 'showposts', -1 );
		}
	}


	/**
	 * Регистрирует стили для админки
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		$options = get_option( $this->post_type_name, [] );
		if ( ! is_array( $options ) ) {
			$options = [];
		}
		if ( ! isset( $options[ 'table_style' ] ) || empty( $options[ 'table_style' ] ) ) {
			$options[ 'table_style' ] = 'default';
		}
		parent::enqueue_styles();
		wp_enqueue_style( 'tablesorter', plugin_dir_url( __FILE__ ) . "css/tablesorter.{$options[ 'table_style' ]}.min.css", array(), '2.31.3', 'all' );
	}


	/**
	 * Регистрирует скрипты для админки
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();
		wp_enqueue_script( 'tablesorter', plugin_dir_url( __FILE__ ) . 'js/jquery.tablesorter.min.js',  array( 'jquery' ), '2.31.3', false );
		wp_add_inline_script( 'tablesorter', 'jQuery( document ).ready( function() { jQuery( ".tablesorter" ).tablesorter(); } );', 'after' );
	}

}