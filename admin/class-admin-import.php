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


	/**
	 * Создаем страницу с формой испорта
	 */
	public function add_page() {
		$this->page_name = add_submenu_page(
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
		ob_start();
		if ( isset( $_POST[ 'action' ] ) && 'run_import' == $_POST[ 'action' ] ) {
			$this->run_import();
		} else {
			$this->render_form();
		}
		$page_content = ob_get_contents();
		ob_end_clean();
		include dirname( __FILE__ ) . '/partials/admin-page.php';
	}



	protected function render_form() {
		?>
			<p><?php printf( __( '<a href="%s">Скачать</a> шаблон файла импорта', $this->plugin_name ), plugin_dir_url( __FILE__ ) . 'helpers/import.csv' ); ?></p>
			<form enctype="multipart/form-data" method="post">
				<?php wp_nonce_field( __FILE__, '_wpnonce', true, true ); ?>
				<input type="hidden" name="action" value="run_import">
				<p>
					<input type="file" id="importfile" name="importfile" accept=".csv" required>
				</p>
				<p>
					<?php submit_button( __( 'Начать импорт', $this->plugin_name ) ); ?>
				</p>
			</form>
		<?php
	} 



	protected function run_import() {
		if (
			true
			&& current_user_can( 'manage_options' )
			&& isset( $_POST[ '_wpnonce' ] )
			&& wp_verify_nonce( $_POST[ '_wpnonce' ], __FILE__ )
			&& isset( $_FILES[ 'importfile' ][ 'name' ] )
			&& 'csv' == pathinfo( $_FILES[ 'importfile' ][ 'name' ], PATHINFO_EXTENSION )
		) {
			add_filter( 'upload_mimes', function ( $mimes ) {
				$mimes[ 'csv' ]  = 'text/plain';
				return $mimes;
			} );
			$temp_file = wp_handle_upload( $_FILES[ 'importfile' ], [ 'test_form' => false ], null );
			if ( $temp_file && empty( $temp_file[ 'error' ] ) ) {
				$competitive_works = $this->get_csv( $temp_file[ 'file' ] );
				if ( empty( $competitive_works ) ) {
					$this->render_admin_notice( __( 'Конкурсные работы не найдены.', $this->plugin_name ), 'warning', true );
					$this->render_form();
				} else {
					$current_user = wp_get_current_user();
					$competitive_works_count_success = 0;
					$competitive_works_count_error = 0;
					$keys = [ 'title', 'excerpt', 'cipher', 'rating', 'authors', 'show_authors', 'work_files', 'reviews', 'invite_files', 'cw_year', 'work_status', 'contest_section', 'category', 'post_tag', 'university' ];
					foreach ( $competitive_works as &$competitive_work ) {
						$post_id = null;
						if ( ! is_array( $competitive_work ) ) {
							$competitive_works_count_error++;
							continue;
						}
						$competitive_work = array_map( 'sanitize_text_field', $competitive_work );
						$competitive_work = array_map( 'trim', $competitive_work );
						$competitive_work = array_merge( array_fill_keys( $keys, '' ), $competitive_work );
						if ( empty( $competitive_work[ 'title' ] ) ) continue;
						$post_id = wp_insert_post( wp_slash( [
							'post_title'     => $competitive_work[ 'title' ],
							'post_status'    => 'publish',
							'post_author'    => $current_user->ID,
							'post_type'      => 'competitive_work',
							'post_excerpt'   => $competitive_work[ 'excerpt' ],
						] ), true );
						if ( null == $post_id || is_wp_error( $post_id ) ) {
							$competitive_works_count_error++;
						} else {
							$competitive_works_count_success++;
							if ( ! empty( $competitive_work[ 'cipher' ] ) ) {
								update_post_meta( $post_id, 'cipher', $competitive_work[ 'cipher' ] );
							}
							if ( ! empty( $competitive_work[ 'rating' ] ) ) {
								update_post_meta( $post_id, 'rating', $competitive_work[ 'rating' ] );
							}
							if ( ! empty( $competitive_work[ 'authors' ] ) ) {
								$competitive_work[ 'authors' ] = $this->parse_persons_from_string( $competitive_work[ 'authors' ] );
								update_post_meta( $post_id, 'authors', $competitive_work[ 'authors' ] );
							}
							if ( ( bool ) $competitive_work[ 'show_authors' ] ) {
								update_post_meta( $post_id, 'show_authors', true );
							}
							foreach ( [ 'work_files', 'reviews', 'invite_files' ] as $key ) {
								$competitive_work[ $key ] = wp_extract_urls( $competitive_work[ $key ] );
								if ( ! empty( $competitive_work[ $key ] ) ) {
									update_post_meta( $post_id, $key, $competitive_work[ $key ] );
								}
							}
							foreach ( [ 'cw_year', 'work_status', 'contest_section' ] as $key ) {
								$competitive_work[ $key ] = preg_split( "/[,;]/", $competitive_work[ $key ], -1, PREG_SPLIT_NO_EMPTY );
								if ( ! empty( $competitive_work[ $key ] ) ) {
									$competitive_work[ $key ] = array_shift( $competitive_work[ $key ] );
									wp_set_object_terms( $post_id, $competitive_work[ $key ], $key, false );
								}
							}
							foreach ( [ 'category', 'post_tag', 'university' ] as $key ) {
								$competitive_work[ $key ] = preg_split( "/[,;]/", $competitive_work[ $key ], -1, PREG_SPLIT_NO_EMPTY );
								if ( ! empty( $competitive_work[ $key ] ) ) {
									wp_set_object_terms( $post_id, $competitive_work[ $key ], $key, false );
								}
							}
						}
					}
					printf( '<p>' . __( 'Добавлнных работ: %s', $this->plugin_name ) . '</p>', $competitive_works_count_success );
					printf( '<p>' . __( 'Ошибок: %s', $this->plugin_name ) . '</p>', $competitive_works_count_error );
				}

			} else {
				$this->render_admin_notice( $temp_file[ 'error' ], 'error', true );
				$this->render_form();
			}

		} else {
			$this->render_admin_notice( __( 'Попробуйте позже или обратитесь к администратору.', $this->plugin_name ), 'warning', true );
			$this->render_form();
		}
	}


	protected function get_csv( $file ) {
		$handle = fopen( $file, "r" ); 
		$result = [];
		$header = [];
		while ( ( $row = fgetcsv( $handle, 0, "," ) ) !== FALSE ) {
			$result[] = $row;
		}
		$header = $result[0];
		if ( ! isset( $result[1] ) ) {
			return false;
		}
		for ( $i = 1; $i < count( $result ); $i++ ) {
			$temp = array();
			for ( $j = 0; $j < count( $header ); $j++ ) {
				$temp[ $header[ $j ] ] = $result[ $i ][ $j ];
			}
			$result[ ($i-1) ] = $temp;
		}
		array_pop( $result );
		fclose( $handle );
		return $result;

	}


}