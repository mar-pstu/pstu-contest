<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за выполение группового действия по 
 * установке статуса отображения информации об авторах
 * конкурсных работ
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminBulkActionShowAuthors extends BulkAction {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->action_name = 'show_authors';
		$this->action_label = __( 'Установка статуса показа информации об авторе конкурсной работы', $this->plugin_name );
	}


	protected function render_competitive_works_fields( &$competitive_works ) {
		wp_nonce_field( __FILE__, "bulk_action_{$this->action_name}_nonce", true, true );
		?>
			<h3><?php _e( 'Список работ', $this->plugin_name ); ?></h3>
			<p>
				<label for="new_status"><?php _e( 'Выбрать новый статус', $this->plugin_name ); ?></label>
				<?php
					echo $this->render_dropdown(
						'new_status', [
							'do_not_show' => __( 'Не показывать', $this->plugin_name ),
							'show' => __( 'Показывать', $this->plugin_name )
						]
					);
					$this->render_submit_action_button( $this->action_name, __( 'Установить статус показа автора', $this->plugin_name ) );
				?>
			</p>
			<p class="small">
				<?php printf( __( 'Количество найденых работ: %s', $this->plugin_name ), count( $competitive_works ) ); ?>
			</p>
			<?php $this->render_select_all_button(); ?>
			<table class="filter-result-table">
				<thead>
					<th><?php _e( 'Отметить работу', $this->plugin_name ); ?></th>
					<th><?php _e( 'Статус показа автора работы', $this->plugin_name ); ?></th>
					<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
					<th><?php _e( 'Название работы', $this->plugin_name ); ?></th>
				</thead>
				<tbody>
					<?php foreach ( $competitive_works as $competitive_work ) : ?>
						<tr>
							<td class="check-column"><?php echo $this->render_checkbox( 'competitive_works[]', $competitive_work->ID, '', [ 'id' => '' ] ); ?></td>
							<td class="show-authors-status"><?php echo ( empty( get_post_meta( $competitive_work->ID, 'show_authors', true ) ) ) ? '' : '<i class="dashicons dashicons-visibility"></i>'; ?></td>
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
					switch ( $_POST[ 'new_status' ] ) {
						case 'do_not_show':
							array_walk( $competitive_works, function ( $post_id ) {
								delete_post_meta( $post_id, 'show_authors'  );
							} );
							$this->add_admin_notice( __( 'Статус показа авторов удалён!' ), 'success' );
							break;
						case 'show':
							array_walk( $competitive_works, function ( $post_id ) {
								add_post_meta( $post_id, 'show_authors', true );
							} );
							$this->add_admin_notice( __( 'Статус показа авторов добавлен!' ), 'success' );
							break;
						default:
							$this->add_admin_notice( __( 'Выбран неверный статус.' ), 'error' );
							break;
					}
				} else {
					$this->add_admin_notice( __( 'Выберите конкурсные работы, которые необходими изменить.' ), 'error' );
				}
			} else {
				$this->add_admin_notice( __( 'Выберите новый статус показа авторов.' ), 'error' );
			}
		} else {
			$this->add_admin_notice( __( 'Действие не выполнено. Обновите страницу.' ), 'error' );
		}
		return;
	}


}