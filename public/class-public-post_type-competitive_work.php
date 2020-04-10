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


}