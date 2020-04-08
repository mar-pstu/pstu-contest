<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс "частей" плагина
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class AdminPart extends Part {


	use Controls;


	/**
	 * Регистрирует зпметку для админпанели
	 * @param    string    $result_text      текст заметки
	 * @param    string    $result_status    статус заметки (default, success, warning, error, info)
	 * @param    bool      $dismissible      возможность закрыть заметку
	 */
	protected function add_admin_notice( $result_text, $result_status = 'default', $dismissible = true ) {
		if ( ! in_array( $result_status, [ 'default', 'success', 'error', 'warning', 'info' ] ) ) {
			$result_status = 'default';
		}
		add_action( 'admin_notices', function () use ( $result_text, $result_status, $dismissible ) {
			?>
				<div id="message" class="notice notice-<?php echo $result_status; ?> <?php echo ( $dismissible ) ? 'is-dismissible' : ''; ?>">
					<p><?php echo $result_text; ?></p>
				</div>
			<?php
		} );
	}


	/**
	 * Регистрирует стили для админки
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
	}


	/**
	 * Регистрирует скрипты для админки
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/admin.js',  array( 'jquery' ), $this->version, false );
	}


}