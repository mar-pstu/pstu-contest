<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за выполение группового действия по 
 * редактированию списка авторов конкурсных работ.
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminBulkActionEditAuthors extends BulkAction {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->action_name = 'edit_authors';
		$this->action_label = __( 'Редактирование списка авторов конкурсных работ', $this->plugin_name );
	}


	protected function render_competitive_works_fields( &$competitive_works ) {
		wp_nonce_field( __FILE__, "bulk_action_{$this->action_name}_nonce", true, true );
		?>
			<h3><?php _e( 'Список работ', $this->plugin_name ); ?></h3>
			<p><?php $this->render_submit_action_button( __( 'Обновить список аторов конкурсных работ', $this->plugin_name ) ); ?></p>
			<p class="small">
				<?php printf( __( 'Количество найденых работ: %s', $this->plugin_name ), count( $competitive_works ) ); ?>
			</p>
			<table class="bulk-action-table">
				<thead>
					<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
					<th><?php _e( 'Название работы', $this->plugin_name ); ?></th>
					<th><?php _e( 'Файлы конкурсной работы', $this->plugin_name ); ?></th>
				</thead>
				<tbody>
					<?php foreach ( $competitive_works as $competitive_work ) : ?>
						<tr>
							<td class="cipher"><?php echo get_post_meta( $competitive_work->ID, 'cipher', true ); ?></td>
							<td class="name"><a target="_blank" href="<?php echo get_the_permalink( $competitive_work->ID ); ?>"><?php echo $competitive_work->post_title; ?></a></td>
							<td class="authors">
								<?php
									$authors = get_post_meta( $competitive_work->ID, 'authors', true );
									echo $this->render_input( 'competitive_works[]', 'hidden', [ 'value' => $competitive_work->ID ] );
									echo $this->render_list_of_templates( 'authors_' . $competitive_work->ID, $authors, array(
										'template' => $this->render_composite_field(
											$this->render_input( 'authors_' . $competitive_work->ID . '[{{data.i}}][last_name]', 'text', [
												'value'    => '{{data.value.last_name}}',
												'class'    => 'form-control',
												'id'       => '',
												'placeholder' => __( 'Фамилия', $this->plugin_name ),
											] ),
											$this->render_input( 'authors_' . $competitive_work->ID . '[{{data.i}}][first_name]', 'text', [
												'value'    => '{{data.value.first_name}}',
												'class'    => 'form-control',
												'id'       => '',
												'placeholder' => __( 'Имя', $this->plugin_name ),
											] ),
											$this->render_input( 'authors_' . $competitive_work->ID . '[{{data.i}}][middle_name]', 'text', [
												'value'    => '{{data.value.middle_name}}',
												'class'    => 'form-control',
												'id'       => '',
												'placeholder' => __( 'Отчество', $this->plugin_name ),
											] )
										)
									) );
								?>
							</td>
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
			if ( isset( $_POST[ 'competitive_works' ] ) && ! empty( $competitive_works = wp_parse_id_list( $_POST[ 'competitive_works' ] ) ) ) {
				foreach ( $competitive_works as $id ) {
					if ( isset( $_POST[ "authors_{$id}" ] ) ) {
						$value = $this->sanitize_person_data( $_POST[ "authors_{$id}" ] );
						if ( empty( $value ) ) {
							delete_post_meta( $id, 'authors' );
						} else {
							update_post_meta( $id, 'authors', $value, false );
						}
					}
				}
				$this->add_admin_notice( __( 'Действие выполнено.' ), 'success' );
			} else {
				$this->add_admin_notice( __( 'Ошибка. Не выбраны конкурсные работы.' ), 'error' );
			}
		} else {
			$this->add_admin_notice( __( 'Действие не выполнено. Обновите страницу.' ), 'error' );
		}
		return;
	}


}