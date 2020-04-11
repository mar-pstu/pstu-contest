<?php


namespace pstu_contest;


/**
 * Запускается при активации плагина
 *
 * @link       http://cct.pstu.edu
 * @since      2.0.0
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 */

/**
 * Запускается при активации плагина.
 * В этом классе находится весь код, который необходимый при активации плагина.
 * @since      2.0.0
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class Activator {

	/**
	 * Действия которые необходимо выполнить при активации
	 * @since    2.0.0
	 */
	public static function activate() {
		$option = get_option( PSTU_CONTEST_NAME, [] );
		if ( ! is_array( $option ) ) {
			$option = [];
		}
		if ( ! isset( $options[ 'version' ] ) || empty( $options[ 'version' ] ) ) {
			$option[ 'version' ] = PSTU_CONTEST_VERSION;
			update_option( PSTU_CONTEST_NAME, $option );
		}
	}

}
