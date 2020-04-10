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
class AdminSettingsManager extends AdminPart {


	protected $page_slug;


	function __construct( $pugin_name, $version ) {
		parent::__construct( $pugin_name, $version );
		$this->page_slug = "{$this->plugin_name}_setting";
	}


	/**
	 * Создаем страницу настроек плагина
	 */
	public function add_page() {
		add_submenu_page(
			'edit.php?post_type=competitive_work',
			__( 'Настройки плагина "Конкурс "Энергетика"', $this->plugin_name ),
			__( 'Настройки', $this->plugin_name ),
			'manage_options',
			$this->page_slug,
			[ $this, 'render_page' ]
		);
	}


	/**
	 * Генерируем html код страницы настроек
	 */
	public function render_page() {
		$tabs = apply_filters( "{$this->plugin_name}_settings-tabs", [] );
		if ( ! empty( $tabs ) ) {
			$current_tab = ( isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET[ 'tab' ], $tabs ) ) ? $_GET[ 'tab' ] : array_keys( $tabs )[ 0 ];
			$page_title = get_admin_page_title();
			$page_content = $this->render_nav_tab_wrapper( $tabs, $current_tab );
			if ( ! empty( $current_tab ) ) {
				ob_start();
				do_action( $this->plugin_name . '_settings-form_' . $current_tab, $this->page_slug );
				$page_content .= ob_get_contents();
			}
			ob_end_clean();
			include dirname( __FILE__ ) . '/partials/admin-page.php';
		}
	}


	/**
	 * Регистрирует настройки плагина
	 */
	public function register_settings() {
		do_action( "{$this->plugin_name}_register_settings", $this->page_slug );
	}



	/**
	 * Генериреут html код вкладок
	 * @param  array  $tabs        массив идентификаторов и заголовков вкладок
	 * @param  string $current_tab идентификатор текущей вкладки
	 * @return string              html-код вкладок
	 */
	protected function render_nav_tab_wrapper( array $tabs, string $current_tab = '' ) {
		$result = [];
		if ( ! empty( $tabs ) ) {
			foreach ( $tabs as $slug => $label ) {
				$result[] = sprintf(
					'<a href="%1$s" class="nav-tab %2$s">%3$s</a>',
					add_query_arg( [ 'tab' => $slug ] ),
					( $slug == $current_tab ) ? 'nav-tab-active' : '',
					$label
				);
			}
		}
		return '<nav class="nav-tab-wrapper wp-clearfix">' . implode( "\r\n", $result ) . '</nav>';
	}



	function render_setting_field( $args ) {
		return;
	}



	/**
	 * Очистка данных
	 *
	 * @var  array    $options
	 */
	function sanitize_callback( $options ) {
		return $options;
	}


}