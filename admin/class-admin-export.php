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
class AdminExport extends AdminPart {


	use Controls;
	use Filter;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->tax_query = ( isset( $_POST[ 'filter' ][ 'tax_query' ] ) ) ? $this->parse_tax_query( $_POST[ 'filter' ][ 'tax_query' ] ): [];
	}


	/**
	 * Создаем страницу с формой испорта
	 */
	public function add_page() {
		add_submenu_page(
			'edit.php?post_type=competitive_work',
			__( 'Экспорт конкурсных работ', $this->plugin_name ),
			__( 'Экспорт', $this->plugin_name ),
			'manage_options',
			$this->plugin_name . '_export',
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
		?>
			<form method="post">
				<?php
					$competitive_works = $this->get_competitive_works( $this->tax_query );
					$this->render_filter_fields( dirname( __FILE__ ), $this->tax_query );
					if ( is_array( $competitive_works ) && ! empty( $competitive_works ) ) {
						$this->render_competitive_works_fields( $competitive_works );
					}
				?>
			</form>
		<?php
		$page_content = ob_get_contents();
		ob_end_clean();
		include dirname( __FILE__ ) . '/partials/admin-page.php';
	}


	/**
	 * Выполняет выборку и формарование файла
	 * @return file выборка
	 */
	public function run_action() {
		if (
			isset( $_POST[ 'action' ] )
			&& 'export' == $_POST[ 'action' ]
			&& isset( $_POST[ "{$this->plugin_name}_export_wpnonce" ] )
			&& wp_verify_nonce( $_POST[ "{$this->plugin_name}_export_wpnonce" ], __FILE__ )
		) {
			$competitive_works = $this->get_competitive_works( $this->tax_query );
			if ( is_array( $competitive_works ) && ! empty( $competitive_works ) ) {
				header( "Content-type: text/csv" );
				header( "Content-Disposition: attachment; filename=file.csv" );
				header( "Pragma: no-cache" );
				header( "Expires: 0" );
				$out = fopen( 'php://output', 'w' );
				fputcsv( $out, [
				/*  0 */ __( 'Название', $this->plugin_name ),
				/*  1 */ __( 'Описание', $this->plugin_name ),
				/*  2 */ __( 'Дата добавления', $this->plugin_name ),
				/*  3 */ __( 'Дата изменения', $this->plugin_name ),
				/*  4 */ __( 'Пользователь', $this->plugin_name ),
				/*  5 */ __( 'Год проведения', $this->plugin_name ),
				/*  6 */ __( 'Рейтинг', $this->plugin_name ),
				/*  7 */ __( 'Шифр', $this->plugin_name ),
				/*  8 */ __( 'Университеты', $this->plugin_name ),
				/*  9 */ __( 'Секция конференции', $this->plugin_name ),
				/* 10 */ __( 'Статус показа авторов', $this->plugin_name ),
				/* 11 */ __( 'Авторы', $this->plugin_name ),
				/* 12 */ __( 'Файлы работ', $this->plugin_name ),
				/* 13 */ __( 'Рецензии', $this->plugin_name ),
				/* 14 */ __( 'Приглашения', $this->plugin_name ),
				/* 15 */ __( 'Статус работы', $this->plugin_name ),
				] );
				foreach ( $competitive_works as $competitive_work ) {
					$post_author = get_userdata( $competitive_work->post_author );
					$cw_years = get_terms( [
						'taxonomy'   => 'cw_year',
						'hide_empty' => false,
						'fields'     => 'names',
						'object_ids' => $competitive_work->ID,
					] );
					$universities = get_terms( [
						'taxonomy'   => 'university',
						'hide_empty' => false,
						'fields'     => 'names',
						'object_ids' => $competitive_work->ID,
					] );
					$contest_sections = get_terms( [
						'taxonomy'   => 'contest_section',
						'hide_empty' => false,
						'fields'     => 'names',
						'object_ids' => $competitive_work->ID,
					] );
					$show_authors = get_post_meta( $competitive_work->ID, 'show_authors', true );
					$authors = get_post_meta( $competitive_work->ID, 'authors', true );
					$work_files = get_post_meta( $competitive_work->ID, 'work_files', true );
					$reviews = get_post_meta( $competitive_work->ID, 'reviews', true );
					$invite_files = get_post_meta( $competitive_work->ID, 'invite_files', true );
					$work_statuses = get_terms( [
						'taxonomy'   => 'work_status',
						'hide_empty' => false,
						'fields'     => 'names',
						'object_ids' => $competitive_work->ID,
					] );
					$row = [];
					$row[  0 ] = $competitive_work->post_title;
					$row[  1 ] = $competitive_work->post_excerpt;
					$row[  2 ] = date( get_option( 'date_format' ), strtotime( $competitive_work->post_date ) );
					$row[  3 ] = date( get_option( 'date_format' ), strtotime( $competitive_work->post_modified ) );
					$row[  4 ] = ( $post_author ) ? $post_author->first_name . ' ' . $post_author->last_name . ' (' . $post_author->user_login . ')' : '';
					$row[  5 ] = ( is_array( $cw_years ) && ! empty( $cw_years ) ) ? wp_sprintf( '%l', $cw_years ) : '';
					$row[  6 ] = get_post_meta( $competitive_work->ID, 'rating', true );
					$row[  7 ] = get_post_meta( $competitive_work->ID, 'cipher', true );
					$row[  8 ] = ( is_array( $universities ) && ! empty( $universities ) ) ? wp_sprintf( '%l', $universities ) : '';
					$row[  9 ] = ( is_array( $contest_sections ) && ! empty( $contest_sections ) ) ? wp_sprintf( '%l', $contest_sections ) : '';
					$row[ 10 ] = ( ( bool ) $show_authors ) ? __( 'Авторы показываются', $this->plugin_name ) : __( 'Авторы скрыты', $this->plugin_name );
					$row[ 11 ] = ( is_array( $authors ) && ! empty( $authors ) ) ? wp_sprintf( '%l', array_map( function ( $item ) {
						return ( is_array( $item ) ) ? implode( " ", $item ) : __( 'Ошибка', $this->plugin_name );
					}, $authors ) ) : '';
					$row[ 12 ] = ( is_array( $work_files ) && ! empty( $work_files ) ) ? wp_sprintf( '%l', $work_files ) : '';
					$row[ 13 ] = ( is_array( $reviews ) && ! empty( $reviews ) ) ? wp_sprintf( '%l', $reviews ) : '';
					$row[ 14 ] = ( is_array( $invite_files ) && ! empty( $invite_files ) ) ? wp_sprintf( '%l', $invite_files ) : '';
					$row[ 15 ] = ( is_array( $work_statuses ) && ! empty( $work_statuses ) ) ? wp_sprintf( '%l', $work_statuses ) : '';
					fputcsv( $out, $row );
				}
				die();
			}
		}
	}


	/**
	 * Выводит список конкурсных рабо
	 * @param  array     &$competitive_works  массив конкурсных работ
	 * @return string                         html-код
	 */
	protected function render_competitive_works_fields( &$competitive_works ) {
		wp_nonce_field( __FILE__, "{$this->plugin_name}_export_wpnonce", true, true );
		?>
			<h3><?php _e( 'Список работ', $this->plugin_name ); ?></h3>
			<p>
				<?php $this->render_submit_action_button( 'export', __( 'Начать экспорт', $this->plugin_name ) ); ?>
			</p>
			<p class="small">
				<?php printf( __( 'Количество найденых работ: %s', $this->plugin_name ), count( $competitive_works ) ); ?>
			</p>
			<table class="filter-result-table">
				<thead>
					<th><?php _e( 'Год проведения', $this->plugin_name ); ?></th>
					<th><?php _e( 'Название работы', $this->plugin_name ); ?></th>
					<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
					<th><?php _e( 'Секция', $this->plugin_name ); ?></th>
					<th><?php _e( 'Статус работы', $this->plugin_name ); ?></th>
				</thead>
				<tbody>
					<?php foreach ( $competitive_works as $competitive_work ) : ?>
						<?php
							$work_statuses = get_terms( [
								'taxonomy'   => 'work_status',
								'hide_empty' => false,
								'fields'     => 'names',
								'object_ids' => $competitive_work->ID,
							] );
							$cw_years = get_terms( [
								'taxonomy'   => 'cw_year',
								'hide_empty' => false,
								'fields'     => 'names',
								'object_ids' => $competitive_work->ID,
							] );
							$contest_sections = get_terms( [
								'taxonomy'   => 'contest_section',
								'hide_empty' => false,
								'fields'     => 'names',
								'object_ids' => $competitive_work->ID,
							] );
						?>
						<tr>
							<td class="cw-year"><?php echo ( is_array( $cw_years ) && ! empty( $cw_years ) ) ? wp_sprintf( '%l', $cw_years ) : ''; ?></td>
							<td class="name"><a target="_blank" href="<?php echo get_the_permalink( $competitive_work->ID ); ?>"><?php echo $competitive_work->post_title; ?></a></td>
							<td class="cipher"><?php echo get_post_meta( $competitive_work->ID, 'cipher', true ); ?></td>
							<td class="contest-sections"><?php echo ( is_array( $contest_sections ) && ! empty( $contest_sections ) ) ? wp_sprintf( '%l', $contest_sections ) : ''; ?></td>
							<td class="work-status"><?php echo ( is_array( $work_statuses ) && ! empty( $work_statuses ) ) ? wp_sprintf( '%l', $work_statuses ) : ''; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php
	}


}