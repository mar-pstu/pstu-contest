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
class AdminBulkActionUniversityChange extends BulkAction {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->action_name = 'edit_university';
		$this->action_label = __( 'Изменение университета', $this->plugin_name );
	}


	protected function render_competitive_works_fields( &$competitive_works ) {
		wp_nonce_field( __FILE__, "bulk_action_{$this->action_name}_nonce", true, true );
		?>
			<h3><?php _e( 'Список работ', $this->plugin_name ); ?></h3>
			<p>
				<label for="new_university"><?php _e( 'Выбрать новый статус', $this->plugin_name ); ?></label>
				<?php
					$new_university_terms = get_terms( [
						'taxonomy'   => 'university',
						'hide_empty' => false,
						'fields'     => 'id=>name',
					] );
					if ( is_array( $new_university_terms ) ) {
						echo $this->render_dropdown( 'new_university', $new_university_terms, [ 'atts' => [
							'class' => 'form-control new-university',
							'id'    => 'new_university',
						] ] );
						$this->render_submit_action_button( __( 'Установить университет', $this->plugin_name ) );
					} else {
						$this->add_admin_notice( __( 'Заполните таксономию "Университеты".' ), 'error' );
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
					<th><?php _e( 'Университет', $this->plugin_name ); ?></th>
					<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
					<th><?php _e( 'Название работы', $this->plugin_name ); ?></th>
				</thead>
				<tbody>
					<?php foreach ( $competitive_works as $competitive_work ) : ?>
						<?php
							$universities = get_terms( [
								'taxonomy'   => 'university',
								'hide_empty' => false,
								'fields'     => 'names',
								'object_ids' => $competitive_work->ID,
							] );
						?>
						<tr>
							<td class="check-column"><?php echo $this->render_checkbox( 'competitive_works[]', $competitive_work->ID, '', [ 'id' => '' ] ); ?></td>
							<td class="university"><?php echo ( is_array( $universities ) && ! empty( $universities ) ) ? wp_sprintf( '%l', $universities ) : ''; ?></td>
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
			if ( isset( $_POST[ 'new_university' ] ) ) {
				if ( isset( $_POST[ 'competitive_works' ] ) && ! empty( $competitive_works = wp_parse_id_list( $_POST[ 'competitive_works' ] ) ) ) {
					$new_university = wp_parse_id_list( $_POST[ 'new_university' ] );
					if ( ! empty( $new_university ) ) {
						array_walk( $competitive_works, function ( $post_id ) use ( $new_university ) {
							wp_set_object_terms( $post_id, $new_university, 'university', false );
						} );
					} else {
						$this->add_admin_notice( __( 'Неверный университет.' ), 'error' );
					}
				} else {
					$this->add_admin_notice( __( 'Выберите конкурсные работы, которые необходими изменить.' ), 'error' );
				}
			} else {
				$this->add_admin_notice( __( 'Выберите новый университет.' ), 'error' );
			}
		} else {
			$this->add_admin_notice( __( 'Действие не выполнено. Обновите страницу.' ), 'error' );
		}
		return;
	}


	/**
	 * Регистрирует стили для админки
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		parent::enqueue_styles();
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/css/select2.min.css', array(), '4.0.12', 'all' );
	}


	/**
	 * Регистрирует скрипты для админки
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js',  array( 'jquery' ), '4.0.12', false );
		wp_add_inline_script( 'select2', "jQuery(document).ready(function() { jQuery('select#new_university').select2();});", 'after' );
	}


}