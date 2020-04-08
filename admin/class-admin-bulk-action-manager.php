<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность админки для
 * типа записи "Конкурсная работа"
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminBulkActionManager extends AdminPart {


	use Controls;


	protected $actions;


	/**
	 * Возвращает список групповых действий
	 * @return   array   список групповых действий
	 */
	protected function get_actions() {
		if ( null == $this->actions ) {
			$this->actions = apply_filters( "{$this->plugin_name}_bulk_action_list", [] );
		}
		return $this->actions;
	}


	/**
	 * Создаем страницу настроек плагина
	 */
	public function add_page() {
		add_submenu_page(
			'edit.php?post_type=competitive_work',
			__( 'Групповые действия', $this->plugin_name ),
			__( 'Групповые действия', $this->plugin_name ),
			'manage_options',
			$this->plugin_name . '_bulk_action',
			[ $this, 'render_page' ]
		);
	}



	/**
	 * Генерируем html код страницы настроек
	 */
	public function render_page() {
		$subscreen = ( isset( $_GET[ 'subscreen' ] ) ) ? $_GET[ 'subscreen' ] : '';
		$page_title = get_admin_page_title();
		$page_content = $this->render_subscreen_choices( $subscreen );
		if ( array_key_exists( $subscreen, $this->get_actions() ) ) {
			ob_start();
			do_action( "{$this->plugin_name}_bulk_action-subscreen_{$subscreen}" );
			$page_content .= ob_get_contents();
			ob_end_clean();
		}
		include dirname( __FILE__ ) . '\partials\admin-page.php';
	}


	/**
	 * Выполняет зарегистрированные действия
	 */
	public function run_action() {
		$action = ( isset( $_POST[ 'action' ] ) ) ? $_POST[ 'action' ] : '';
		if ( array_key_exists( $action, $this->get_actions() ) ) {
			do_action( "{$this->plugin_name}_bulk_action-run_{$action}" );
		}
	}


	/**
	 * Формирует html-код экрана с формой группового действия.
	 * @param     string    $subscreen    идентификатор текущего "экрана"
	 * @return    string                  html-код экрана
	 */
	protected function render_subscreen_choices( $subscreen = '' ) {
		$html = '';
		if ( ! empty( $this->get_actions() ) ) {
			$choices = [];
			foreach ( $this->get_actions() as $action_name => $action_label ) {
				$choices[ add_query_arg( [ 'subscreen' => $action_name ] ) ] = $action_label;
			}
			return sprintf(
				'<p><label for="subscreen-choice">%1$s</label> %2$s </p>',
				__( 'Выберите групповое действие', $this->plugin_name ),
				$this->render_dropdown( 'subscreen-choice', $choices, [
					'selected' => add_query_arg( [ 'subscreen' => $subscreen ] ),
					'show_option_none'  => __( 'Не выбрано', $this->plugin_name ),
					'option_none_value' => add_query_arg( [ 'subscreen' => '' ] ),
					'atts'     => [
						'id'       => 'subscreen-choice',
						'onchange' => 'if ( this.value ) window.location.href = this.value',
					],
				] )
			);
		}
		return $html;
	}


	/**
	 * Регистрирует необходимые скрипты
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();
		wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker' );
	}



}