<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за выполение группового действия по 
 * изменению Сеции конкурсных работ. Секции реалихованы
 * как пользовательская таксономия.
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminBulkActionContestSectionChange extends BulkAction {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->action_name = 'change_contest_section';
		$this->action_label = __( 'Изменение секции', $this->plugin_name );
	}


	protected function render_competitive_works_fields( &$competitive_works ) {
		wp_nonce_field( __FILE__, "bulk_action_{$this->action_name}_nonce", true, true );
		?>
			<h3><?php _e( 'Список работ', $this->plugin_name ); ?></h3>
			<p>
				<label for="new-contest-sections"><?php _e( 'Выбрать новую секцию', $this->plugin_name ); ?></label>
				<?php
					$new_contest_section_terms = get_terms( [
						'taxonomy'   => 'contest_section',
						'hide_empty' => false,
						'fields'     => 'id=>name',
					] );
					if ( is_array( $new_contest_section_terms ) ) {
						echo $this->render_dropdown( 'new_contest_sections[]', $new_contest_section_terms, [ 'atts' => [
							'class'    => 'form-control new-contest-sections',
							'id'       => 'new-contest-sections',
						] ] );
						$this->render_submit_action_button( $this->action_name, __( 'Установить секцию', $this->plugin_name ) );
					} else {
						$this->add_admin_notice( __( 'Заполните таксономию "Секции".' ), 'error' );
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
					<th><?php _e( 'Секция', $this->plugin_name ); ?></th>
					<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
					<th><?php _e( 'Название работы', $this->plugin_name ); ?></th>
				</thead>
				<tbody>
					<?php foreach ( $competitive_works as $competitive_work ) : ?>
						<?php
							$contest_sections = get_terms( [
								'taxonomy'   => 'contest_section',
								'hide_empty' => false,
								'fields'     => 'names',
								'object_ids' => $competitive_work->ID,
							] );
						?>
						<tr>
							<td class="check-column"><?php echo $this->render_checkbox( 'competitive_works[]', $competitive_work->ID, '', [ 'id' => '' ] ); ?></td>
							<td class="contest_section"><?php echo ( is_array( $contest_sections ) && ! empty( $contest_sections ) ) ? wp_sprintf( '%l', $contest_sections ) : ''; ?></td>
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
			if ( isset( $_POST[ 'new_contest_sections' ] ) ) {
				if ( isset( $_POST[ 'competitive_works' ] ) && ! empty( $competitive_works = wp_parse_id_list( $_POST[ 'competitive_works' ] ) ) ) {
					$new_contest_sections = wp_parse_id_list( $_POST[ 'new_contest_sections' ] );
					if ( ! empty( $new_contest_sections ) ) {
						array_walk( $competitive_works, function ( $post_id ) use ( $new_contest_sections ) {
							wp_set_object_terms( $post_id, $new_contest_sections, 'contest_section', false );
						} );
					} else {
						$this->add_admin_notice( __( 'Неверная секция.' ), 'error' );
					}
				} else {
					$this->add_admin_notice( __( 'Выберите конкурсные работы, которые необходими изменить.' ), 'error' );
				}
			} else {
				$this->add_admin_notice( __( 'Выберите новую секцию' ), 'error' );
			}
		} else {
			$this->add_admin_notice( __( 'Действие не выполнено. Обновите страницу.' ), 'error' );
		}
		return;
	}


}