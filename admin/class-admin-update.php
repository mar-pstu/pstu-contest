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


	/**
	 * Параметры плагина
	 * @var array
	 */
	protected $options;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->tab_name = 'update';
		$this->tab_label = __( 'Обновление', $this->plugin_name );
		$this->options = get_option( $this->plugin_name, [] );
		if ( ! is_array( $this->options ) ) {
			$this->options = [];
		}
		$this->options = array_merge( [
			'version' => '',
		], $this->options );
	}


	public function check_update() {
		if ( empty( $this->options[ 'version' ] ) ) {
			$this->add_admin_notice( __( 'Невозможно определить версию базы данных. Возможно при установке плагина произошка ошибка. Если плагин установлен поверх версии 1.0.0, то сделайте резервную копию и запустите обновление, активируей плагин заново.', $this->plugin_name ), 'error', false );
		} elseif ( $this->version < $this->options[ 'version' ] ) {
			$this->add_admin_notice( __( 'Установлена устаревшая версия плагина. Сделайте резервную копию и обновитесь.', $this->plugin_name ), 'error', false );
		} elseif ( $this->version > $this->options[ 'version' ] ) {
			$this->add_admin_notice( __( 'Плагин запущен с устаревшей версией базы данных. Для корректной работы необходимо обновить базу данных. Сделайте резервную копию и нажмите кнопку "Запустить обновление".', $this->plugin_name ), 'error', false );
		}
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
		?>
			<p><?php printf( __( 'Версия плагина: %s', $this->plugin_name ), $this->version ); ?></p>
			<p><?php printf( __( 'Версия базы данных: %s', $this->plugin_name ), ( empty( $this->options[ 'version' ] ) ) ? __( 'неопределена', $this->plugin_name ) : $this->options[ 'version' ] ); ?></p>
		<?
		if ( $this->version == $this->options[ 'version' ] ) {
			?>
				<p>
					<?php printf( __( 'Версия плагина совпадает с версией базы данных. Дополнительные действия не требуются.', $this->plugin_name ), $this->version ); ?>
				</p>
			<?php
		} elseif ( $this->version > $this->options[ 'version' ] ) {
			?>
				<form method="post">
					<?php
						wp_nonce_field( __FILE__, 'update_nonce' );
						echo $this->render_input( 'tab', 'hidden', [ 'value' => $this->tab_name ] );
						echo $this->render_input( 'action', 'hidden', [ 'value' => 'db_update' ] );
						submit_button( __( 'Запустить обновление', $this->plugin_name ), 'primary', 'submit', true, null );
					?>
				</form>
			<?php
		}
	}



	/**
	 * Генерируем html код страницы настроек
	 */
	public function run_tab() {
		if (
			isset( $_POST[ 'tab' ] )
			&& $this->get_tab_name() == $_POST[ 'tab' ]
			&& isset( $_POST[ 'action' ] )
			&& 'db_update' == $_POST[ 'action' ]
			&& isset( $_POST[ 'update_nonce' ] )
			&& wp_verify_nonce( $_POST[ 'update_nonce' ], __FILE__ )
		) {
			$this->update_db();
		}
	}


	/**
	 * Переносит данные со старого формата в новый, т.е.
	 * с версии 1.0.0 на версию 2.0.0
	 */
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
					$old_author_1 = array_map( 'trim', $old_author_1 );
					$old_authors[] = trim( implode( " ", $old_author_1 ) );
				}
				if ( is_array( $old_author_2 ) && ! empty( $old_author_2 ) ) {
					$old_author_2 = array_map( 'trim', $old_author_2 );
					$old_authors[] = trim( implode( " ", $old_author_2 ) );
				}
				if ( is_array( $old_author_3 ) && ! empty( $old_author_3 ) ) {
					$old_author_3 = array_map( 'trim', $old_author_3 );
					$old_authors[] = trim( implode( " ", $old_author_3 ) );
				}
				$new_authors = [];
				foreach ( $old_authors as &$old_author ) {
					$old_author = explode( " ", $old_author );
					$old_author = array_filter( $old_author, function( $element ) {
						return ! empty( trim( $element ) );
					} );
					if ( ! empty( $old_author ) ) {
						$new_author = [];
						if ( count( $old_author ) > 3 ) {
							$new_author[ 'middle_name' ] = array_pop( $old_author );
							$new_author[ 'first_name' ] = array_pop( $old_author );
							$new_author[ 'last_name' ] = trim( implode( " ", $old_author ) );
						} else {
							$count = 0;
							foreach ( [ 'last_name', 'first_name', 'middle_name' ] as $key ) {
								$author[ $key ] = ( isset( $item[ $count ] ) ) ? $item[ $count ] : '';
								$count++;
							}
						}
						if ( ! empty( $new_author ) ) {
							$new_authors[] = $new_author;
						}
					}
				}
				if ( empty( $new_authors ) ) {
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author' );
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author2' );
					delete_post_meta( $competitive_work->ID, '_pstu_competitive_work_author3' );
				} else {
					if ( ( bool ) update_post_meta( $competitive_work->ID, 'authors', $new_authors ) ) {
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

				// университеты
				$old_universities = explode( ",", $competitive_work->post_excerpt );
				if ( is_array( $old_universities ) && ! empty( $old_universities ) ) {
					$new_universities_ids = [];
					foreach ( $old_universities as &$old_university ) {
						$old_university = strip_tags( $old_university );
						$old_university = preg_replace( '/([^\pL\pN\pP\pS\pZ])|([\xC2\xA0])/u', ' ', $old_university );
						$old_university = trim( preg_replace( '/\s{2,}/', ' ', $old_university ) );
						if ( empty( $old_university ) ) continue;
						$old_university_term = get_term_by( 'name', $old_university, 'university', OBJECT, 'raw' );
						if ( ! $old_university_term || is_wp_error( $old_university_term ) ) {
							$old_university_insert_data = wp_insert_term( $old_university, 'university', [] );
							if ( ! is_wp_error( $old_university_insert_data ) ) {
								$new_universities_ids[] = $old_university_insert_data[ 'term_id' ];
							}
						} else {
							$new_universities_ids[] = $old_university_term->term_id;
						}
					}
					wp_update_post( wp_slash( [
						'ID'           => $competitive_work->ID,
						'post_excerpt' => '',
					] ) );
					wp_set_object_terms( $competitive_work->ID, $new_universities_ids, 'university', false );
				}
			
			}
		}
		$this->option[ 'version' ] = $this->version;
		update_option( $this->plugin_name, $this->option );
		// remove_action( 'admin_notices', [ $this, '' ], 10 );
	}



}