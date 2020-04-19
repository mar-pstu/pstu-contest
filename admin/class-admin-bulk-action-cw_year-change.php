<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за выполение группового действия по 
 * установке статуса конкурсной работы. Статус реалихован
 * как пользовательская таксономия.
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminBulkActionCWYearChange extends BulkAction {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->action_name = 'cw_year_change';
		$this->action_label = __( 'Изменение года проведения конкурсных работ', $this->plugin_name );
	}


	protected function render_competitive_works_fields( &$competitive_works ) {
		wp_nonce_field( __FILE__, "bulk_action_{$this->action_name}_nonce", true, true );
		?>
			<h3><?php _e( 'Список работ', $this->plugin_name ); ?></h3>
			<p>
				<label for="new_cw_year"><?php _e( 'Выбрать новый год проведения', $this->plugin_name ); ?></label>
				<?php
					$new_cw_year_terms = get_terms( [
						'taxonomy'   => 'cw_year',
						'hide_empty' => false,
						'fields'     => 'id=>name',
					] );
					if ( is_array( $new_cw_year_terms ) ) {
						echo $this->render_dropdown( 'new_cw_year', $new_cw_year_terms );
						$this->render_submit_action_button( $this->action_name, __( 'Установить новый статус конкурсных работ', $this->plugin_name ) );
					} else {
						$this->add_admin_notice( __( 'Заполните таксономию статусов.' ), 'error' );
					}
				?>
			</p>
			<p class="small">
				<?php printf( __( 'Количество найденых работ: %s', $this->plugin_name ), count( $competitive_works ) ); ?>
			</p>
			<?php $this->render_select_all_button(); ?>
			<table class="filter-result-table">
				<thead>
					<th><?php _e( 'Отметить работу', $this->plugin_name ); ?></th>
					<th><?php _e( 'Год проведения', $this->plugin_name ); ?></th>
					<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
					<th><?php _e( 'Название работы', $this->plugin_name ); ?></th>
					<th><?php _e( 'Дата добавления', $this->plugin_name ); ?></th>
				</thead>
				<tbody>
					<?php foreach ( $competitive_works as $competitive_work ) : ?>
						<?php
							$cw_years = get_terms( [
								'taxonomy'   => 'cw_year',
								'hide_empty' => false,
								'fields'     => 'names',
								'object_ids' => $competitive_work->ID,
							] );
						?>
						<tr>
							<td class="check-column"><?php echo $this->render_checkbox( 'competitive_works[]', $competitive_work->ID, '', [ 'id' => '' ] ); ?></td>
							<td class="cw-year"><?php echo ( is_array( $cw_years ) && ! empty( $cw_years ) ) ? wp_sprintf( '%l', $cw_years ) : ''; ?></td>
							<td class="cipher"><?php echo get_post_meta( $competitive_work->ID, 'cipher', true ); ?></td>
							<td class="name"><a target="_blank" href="<?php echo get_the_permalink( $competitive_work->ID ); ?>"><?php echo $competitive_work->post_title; ?></a></td>
							<td class="date"><?php echo date( get_option( 'date_format' ), strtotime( $competitive_work->post_date ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php
	}


	/**
	 * Выполняет групповое действие
	 *
	 * @since    2.0.0
	 */
	public function run_action() {
		if ( isset( $_POST[ "bulk_action_{$this->action_name}_nonce" ] ) && wp_verify_nonce( $_POST[ "bulk_action_{$this->action_name}_nonce" ], __FILE__ ) ) {
			if ( isset( $_POST[ 'new_cw_year' ] ) ) {
				if ( isset( $_POST[ 'competitive_works' ] ) && ! empty( $competitive_works = wp_parse_id_list( $_POST[ 'competitive_works' ] ) ) ) {
					$new_cw_year = wp_parse_id_list( $_POST[ 'new_cw_year' ] );
					if ( ! empty( $new_cw_year ) ) {
						array_walk( $competitive_works, function ( $post_id ) use ( $new_cw_year ) {
							wp_set_object_terms( $post_id, $new_cw_year, 'cw_year', false );
						} );
					} else {
						$this->add_admin_notice( __( 'Неверный год проведения.' ), 'error' );
					}
				} else {
					$this->add_admin_notice( __( 'Выберите конкурсные работы, которые необходими изменить.' ), 'error' );
				}
			} else {
				$this->add_admin_notice( __( 'Выберите новый год проведения конкурсных работ.' ), 'error' );
			}
		} else {
			$this->add_admin_notice( __( 'Действие не выполнено. Обновите страницу.' ), 'error' );
		}
		return;
	}


}