<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за страницу экспорта
 * конкурсных работ
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminUpdateTab extends AdminPart {


	use Controls;


	/**
	 * Идентификатор вкладки настроек
	 * @var string
	 */
	protected $tab_name;


	/**
	 * Название вкладки настроек
	 * @var string
	 */
	protected $tab_label;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->tab_name = 'update';
		$this->tab_label = __( 'Обновление', $this->plugin_name );
	}


	/**
	 * Фильтр, который добавляет вкладку с опциями
	 * на страницу настроектплагина
	 * @since    2.0.0
	 * @param    array     $tabs     исходный массив вкладок идентификатор вкладки=>название
	 * @return   array     $tabs     отфильтрованный массив вкладок идентификатор вкладки=>название
	 */
	public function add_settings_tab( $tabs ) {
		$tabs[ $this->tab_name ] = $this->tab_label;
		return $tabs;
	}


	/**
	 * Возвращает идентификатор вкладки для регистрации на 
	 * странице настроек плагина
	 * @return   string    идентификатор вкладки
	 */
	public function get_tab_name() {
		return $this->tab_name;
	}


	/**
	 * Генерируем html код страницы настроек
	 */
	public function render_tab() {
		$options = get_option( $this->plugin_name, [] );
		if ( ! is_array( $options ) ) {
			$options = [];
		}
		$options = array_merge( [
			'version' => '',
		], $options );
		?>
			<p><?php printf( __( 'Версия плагина: %s', $this->plugin_name ), $this->version ); ?></p>
			<p><?php printf( __( 'Версия базы данных: %s', $this->plugin_name ), ( empty( $options[ 'version' ] ) ) ? __( 'неопределена', $this->plugin_name ) : $options[ 'version' ] ); ?></p>
		<?
		if ( $this->version == $options[ 'version' ] ) {
			?>
				<p>
					<?php printf( __( 'Версия плагина совпадает с версией базы данных. Обновление не требуется.', $this->plugin_name ), $this->version ); ?>
				</p>
			<?php
		} elseif ( empty( $options[ 'version' ] ) ) {
			?>
				<p>
					<?php _e( 'Невозможно определить версию базы данных. Возможно при установке плагина произошка ошибка.', $this->plugin_name ); ?>
				</p>
			<?php
		} elseif ( $this->version < $options[ 'version' ] ) {
			?>
				<p>
					<?php _e( 'Установлена устаревшая версия плагина. Сделайте резервную копию и обновитесь.', $this->plugin_name ); ?>
				</p>
			<?php
		} else {
			?>
				<p>
					<?php _e( 'Плагин запущен с устаревшей версией базы данных. Для корректной работы необходимо обновить базу данных. Сделайте резервную копию и нажмите кнопку "Запустить обновление".', $this->plugin_name ); ?>
				</p>
				<form method="post">
					<?php
						wp_nonce_field( __FILE__, 'update_nonce' );
						echo $this->render_input( 'action', 'hidden', [ 'value' => 'update_db' ] );
						submit_button( __( 'Запустить обновление', $this->plugin_name ), 'primary', 'submit', true, null );
					?>
				</form>
			<?php
		}
	}



	/**
	 * Генерируем html код страницы настроек
	 */
	public function run_action() {
		echo "string";
		if (
			true
			// isset( $_POST[ 'action' ] )
			// && isset( $_POST[ 'update_nonce' ] )
			// && wp_verify_nonce( $_POST[ 'update_nonce' ], __FILE__ )
		) {
			$this->add_admin_notice( __( 'старт' ), 'default' );
		}
	}



	protected function update_db() {
		$competitive_works = get_posts( [
			'numberposts' => -1,
			'post_type'   => 'competitive_work',
			'suppress_filters' => true,
		] );
		if ( is_array( $competitive_works ) && ! empty( $competitive_works ) ) {
			foreach ( $competitive_works as $competitive_work ) {
				
				// шифр
				$old_cipher = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_cipher', true );
				if ( empty( $old_cipher ) ) {
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_cipher' );
				} else {
					if ( ( bool ) update_post_meta( $competitive_work->ID, 'cipher', $old_cipher ) ) {
						delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_cipher' );
					}
				}
				
				// рейтинг
				$old_rating = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_rating', true );
				if ( empty( $old_rating ) ) {
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_rating' );
				} else {
					if ( ( bool ) update_post_meta( $competitive_work->ID, 'rating', $old_rating ) ) {
						delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_rating' );
					}
				}

				// авторы
				$old_author_1 = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_author', true );
				$old_author_2 = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_author2', true );
				$old_author_3 = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_author3', true );
				$old_authors = [];
				if ( is_array( $old_author_1 ) && ! empty( $old_author_1 ) ) {
					$old_author_1 = $this->sanitize_person_data( $old_author_1 );
					if ( ! empty( $old_author_1 ) ) {
						$old_authors[] = $old_author_1;
					}
				}
				if ( is_array( $old_author_2 ) && ! empty( $old_author_2 ) ) {
					$old_author_2 = $this->sanitize_person_data( $old_author_2 );
					if ( ! empty( $old_author_2 ) ) {
						$old_authors[] = $old_author_2;
					}
				}
				if ( is_array( $old_author_3 ) && ! empty( $old_author_3 ) ) {
					$old_author_3 = $this->sanitize_person_data( $old_author_3 );
					if ( ! empty( $old_author_3 ) ) {
						$old_authors[] = $old_author_3;
					}
				}
				if ( empty( $old_authors ) ) {
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author' );
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author2' );
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author3' );
				} else {
					if ( ( bool ) update_post_meta( $competitive_work->ID, 'authors', $old_authors ) ) {
						delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author' );
						delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author2' );
						delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author3' );
					}
				}

				
				// рецензии
				$old_reviews = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_reviews', true );
				if ( empty( $old_reviews ) ) {
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_reviews' );
				} else {
					if ( ( bool ) update_post_meta( $competitive_work->ID, 'reviews', [ $old_reviews ] ) ) {
						delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_reviews' );
					}
				}
				
				// файлы
				$old_work_files = get_post_meta( $competitive_work->ID, '_pstu_competitive_work_file', true );
				if ( empty( $old_work_files ) ) {
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_file' );
				} else {
					if ( ( bool ) update_post_meta( $competitive_work->ID, 'work_files', [ $old_work_files ] ) ) {
						delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_file' );
					}
				}
				
				// приглашения
				$old_invite_files = get_post_meta( $competitive_work->ID, '_pstu_invite_file', true );
				if ( empty( $old_invite_files ) ) {
					delete_post_meta( $competitive_work->ID, '_pstu_invite_file' );
				} else {
					if ( ( bool ) update_post_meta( $competitive_work->ID, 'invite_files', [ $old_invite_files ] ) ) {
						delete_post_meta( $competitive_work->ID, '_pstu_invite_file' );
					}
				}
			
			}
		}
	}



}