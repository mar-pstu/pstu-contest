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
class AdminBulkActionStatusChange extends BulkAction {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->action_name = 'status_change';
		$this->action_label = __( 'Изменение статуса конкурсных работ', $this->plugin_name );
	}


	protected function render_competitive_works_fields( &$competitive_works ) {
		wp_nonce_field( __FILE__, "bulk_action_{$this->action_name}_nonce", true, true );
		?>
			<h3><?php _e( 'Список работ', $this->plugin_name ); ?></h3>
			<p>
				<label for="new_status"><?php _e( 'Выбрать новый статус', $this->plugin_name ); ?></label>
				<?php
					$new_status_terms = get_terms( [
						'taxonomy'   => 'work_status',
						'hide_empty' => false,
						'fields'     => 'id=>name',
					] );
					if ( is_array( $new_status_terms ) ) {
						echo $this->render_dropdown( 'new_status', $new_status_terms );
						$this->render_submit_action_button( __( 'Установить новый статус конкурсных работ', $this->plugin_name ) );
					} else {
						$this->add_admin_notice( __( 'Заполните таксономию статусов.' ), 'error' );
					}
				?>
			</p>
			<p class="small">
				<?php printf( __( 'Количество найденых работ: %s', $this->plugin_name ), count( $competitive_works ) ); ?>
			</p>
			<?php $this->render_select_all_button(); ?>
			<table class="bulk-action-table">
				<thead>
					<th><?php _e( 'Отметить работу', $this->plugin_name ); ?></th>
					<th><?php _e( 'Статус работы', $this->plugin_name ); ?></th>
					<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
					<th><?php _e( 'Название работы', $this->plugin_name ); ?></th>
				</thead>
				<tbody>
					<?php foreach ( $competitive_works as $competitive_work ) : ?>
						<tr>
							<td class="check-column"><?php echo $this->render_checkbox( 'competitive_works[]', $competitive_work->ID, '', [ 'id' => '' ] ); ?></td>
							<td class="work-status"><?php echo get_the_term_list( $competitive_work->ID, 'work_status', '<ul class="list-inline"><li>', '</li><li>', '</li></ul>' ); ?></td>
							<td class="cipher"><?php echo get_post_meta( $competitive_work->ID, 'cipher', true ); ?></td>
							<td class="name"><a target="_blank" href="<?php echo get_the_permalink( $competitive_work->ID ); ?>"><?php echo $competitive_work->post_title; ?></a></td>
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
			if ( isset( $_POST[ 'new_status' ] ) ) {
				if ( isset( $_POST[ 'competitive_works' ] ) && ! empty( $competitive_works = wp_parse_id_list( $_POST[ 'competitive_works' ] ) ) ) {
					$new_status = wp_parse_id_list( $_POST[ 'new_status' ] );
					if ( ! empty( $new_status ) ) {
						array_walk( $competitive_works, function ( $post_id ) use ( $new_status ) {
							wp_set_object_terms( $post_id, $new_status, 'work_status', false );
						} );
					} else {
						$this->add_admin_notice( __( 'Неверный статус конкурсных работ.' ), 'error' );
					}
				} else {
					$this->add_admin_notice( __( 'Выберите конкурсные работы, которые необходими изменить.' ), 'error' );
				}
			} else {
				$this->add_admin_notice( __( 'Выберите новый статус конкурсных работ.' ), 'error' );
			}
		} else {
			$this->add_admin_notice( __( 'Действие не выполнено. Обновите страницу.' ), 'error' );
		}
		return;
	}


	/**
	 * Регистрирует стили для админки
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
	}


}