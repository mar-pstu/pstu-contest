<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за выполение группового действия по 
 * редактированию списка рецензиц к конкурсной
 * работе.
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminBulkActionEditReviews extends BulkAction {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->action_name = 'edit_reviews';
		$this->action_label = __( 'Редактирование списка рецензий', $this->plugin_name );
	}


	public function render_custom_filter_fields( string $path = '', array $tax_query = [] ) {
		if (
			isset( $_GET[ 'page' ] )
			&& "{$this->plugin_name}_bulk_action" == $_GET[ 'page' ]
			&& isset( $_GET[ 'subscreen' ] )
			&& $this->action_name == $_GET[ 'subscreen' ]
		) {
			$id = '';
			$label = __( 'Рецензии', $this->plugin_name );
			$atts = [];
			if ( isset( $this->custom_query[ 'choose_cw_without_reviews' ] ) && 'on' == $this->custom_query[ 'choose_cw_without_reviews' ] ) {
				$atts[ 'checked' ] = 'checked';
			}
			$control = $this->render_checkbox( "filter[custom_query][choose_cw_without_reviews]", 'on', __( 'выбрать записи без файлов рецензий', $this->plugin_name ), $atts );
			include $path . '/partials/form-group.php';
		}
	}


	public function custom_fields_result_args( array $args = [] ) {
		if ( isset( $this->custom_query[ 'choose_cw_without_reviews' ] ) && 'on' == $this->custom_query[ 'choose_cw_without_reviews' ] ) {
			$args[ 'meta_query' ][] = [
				'key'     => 'work_files',
				'compare' => 'NOT EXISTS',
			];
		}
		return $args;
	}


	protected function render_competitive_works_fields( &$competitive_works ) {
		wp_nonce_field( __FILE__, "bulk_action_{$this->action_name}_nonce", true, true );
		?>
			<h3><?php _e( 'Список работ', $this->plugin_name ); ?></h3>
			<p><?php $this->render_submit_action_button( $this->action_name, __( 'Обновить список отзывов у конкурсных работ', $this->plugin_name ) ); ?></p>
			<p class="small">
				<?php printf( __( 'Количество найденых работ: %s', $this->plugin_name ), count( $competitive_works ) ); ?>
			</p>
			<table class="filter-result-table">
				<thead>
					<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
					<th><?php _e( 'Название работы', $this->plugin_name ); ?></th>
					<th><?php _e( 'Рецензии', $this->plugin_name ); ?></th>
				</thead>
				<tbody>
					<?php foreach ( $competitive_works as $competitive_work ) : ?>
						<tr>
							<td class="cipher"><?php echo get_post_meta( $competitive_work->ID, 'cipher', true ); ?></td>
							<td class="name"><a target="_blank" href="<?php echo get_the_permalink( $competitive_work->ID ); ?>"><?php echo $competitive_work->post_title; ?></a></td>
							<td class="reviews">
								<?php
									$reviews = get_post_meta( $competitive_work->ID, 'reviews', true );
									echo $this->render_input( 'competitive_works[]', 'hidden', [ 'value' => $competitive_work->ID ] );
									echo $this->render_list_of_templates( 'reviews_' . $competitive_work->ID, $reviews, array(
										'template' => $this->render_file_choice( 'reviews_' . $competitive_work->ID . '[{{data.i}}]', 'text', [
											'value'    => '{{data.value}}',
											'class'    => 'form-control',
											'id'       => '',
										] ) )
									);
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
					if ( isset( $_POST[ "reviews_{$id}" ] ) ) {
						$value = $this->sanitize_url_list( $_POST[ "reviews_{$id}" ] );
						if ( empty( $value ) ) {
							delete_post_meta( $id, 'reviews' );
						} else {
							update_post_meta( $id, 'reviews', $value, false );
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