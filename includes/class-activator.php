<?php


namespace pstu_contest;


/**
 * Запускается при активации плагина
 *
 * @link       http://cct.pstu.edu
 * @since      2.0.0
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 */

/**
 * Запускается при активации плагина.
 *
 * В этом классе находится весь код, который необходимый при активации плагина.
 *
 * @since      2.0.0
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class Activator {

	/**
	 * Действия которые необходимо выполнить при активации
	 *
	 * @since    2.0.0
	 */
	public static function activate() {
		$competitive_works = get_posts( [
			'numberposts' => -1,
			'post_type'   => 'competitive_work',
			'suppress_filters' => true,
		] );
		if ( is_array( $competitive_works ) && ! empty( $competitive_works ) ) {
			foreach ( $competitive_works as $competitive_work ) {
				
				// шифр
				$old_cipher = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_cipher', true );
				if ( ! empty( $old_cipher ) ) {
					update_post_meta( $competitive_work->ID, 'cipher', $old_cipher );
				}
				
				// рейтинг
				$old_rating = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_rating', true );
				if ( ! empty( $old_rating ) ) {
					update_post_meta( $competitive_work->ID, 'rating', $old_rating );
				}

				// авторы
				$old_author_1 = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_author', true );
				$old_author_2 = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_author2', true );
				$old_author_3 = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_author3', true );
				$old_authors = [];
				
				// рецензии
				$old_reviews = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_reviews', true );
				if ( ! empty( $old_reviews ) ) {
					update_post_meta( $competitive_work->ID, 'reviews', [ $old_reviews ] );
				}
				
				// файлы
				$old_work_files = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_file', true );
				if ( ! empty( $old_work_files ) ) {
					update_post_meta( $competitive_work->ID, 'work_files', [ $old_work_files ] );
				}
				
				// приглашения
				$old_invite_files = get_post_meta( $competitive_work->ID, '_pstu_invite_file', true );
				if ( ! empty( $old_invite_files ) ) {
					update_post_meta( $competitive_work->ID, 'invite_files', [ $old_invite_files ] );
				}
			
			}
		}
	}

}
