<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс создания группового действия
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class BulkAction extends AdminPart {


	use Controls;
	use Filter;


	/**
	 * Имя (идентификатор) "действия"
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    Уникальный идентификтор "действия"
	 */
	protected $action_name;

	/**
	 * Название "действия"
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    Название "действия"
	 */
	protected $action_label;


	/**
	 * Параметры выборки фильтра по таксономии
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    Параметры выборки
	 */
	protected $tax_query;


	/**
	 * Инициализация класса и установка его свойства.
	 *
	 * @since    2.0.0
	 * @param    string    $plugin_name       Имя плагин и слаг метаполей
	 * @param    string    $version           Текущая версия
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->tax_query = ( isset( $_POST[ 'filter' ][ 'tax_query' ] ) ) ? $this->parse_tax_query( $_POST[ 'filter' ][ 'tax_query' ] ): [];
	}


	/**
	 * Возвращает имя (идентификатор) "действия"
	 * @return   string    Уникальный идентификтор "действия"
	 */
	public function get_action_name() {
		return $this->action_name;
	}


	/**
	 * Возвращает название "действия"
	 * @return   string    Название "действия"
	 */
	public function get_action_label() {
		return $this->action_label;
	}



	/**
	 * Выполняет действие
	 */
	public function run_action() {
		return;
	}


	/**
	 * Формирует и выводит html-код под-экрнана
	 */
	public function render_subscreen() {
		?>
			<form method="post" action="<?php echo add_query_arg( [ 'screen' => $this->action_name ] ); ?>">
				<?php
					$this->render_filter_fields( dirname( __FILE__ ), $this->tax_query );
					$competitive_works = $this->get_competitive_works( $this->tax_query );
					if ( empty( $competitive_works ) ) {
						?>
							<div id="message" class="notice notice-warning is-dismissible">
								<p><?php _e( 'Конкурсные работы не найдены.', $this->plugin_name ); ?></p>
							</div>
						<?php
					} else {
						$this->render_competitive_works_fields( $competitive_works );
					}
				?>
			</form>
		<?php
	}


	public function render_select_all_button( $args = [] ) {
		$args = array_merge( [
			'select_all_label' => __( 'Выбрать все работы', $this->plugin_name ),
			'unselect_all'     => __( 'Снять выделение со всех работ', $this->plugin_name ),
			'checkbox_names'   => 'competitive_works[]',
		], $args );
		?>
			<p>
				<button class="button" type="button" onclick="document.querySelectorAll('input[type=checkbox][name=\'<?php echo $args[ 'checkbox_names' ]; ?>\']').forEach(function(el){el.checked=true});">
					<?php echo $args[ 'select_all_label' ]; ?>
				</button>
				<button class="button" type="button" onclick="document.querySelectorAll('input[type=checkbox][name=\'<?php echo $args[ 'checkbox_names' ]; ?>\']').forEach(function(el){el.checked=false});">
					<?php echo $args[ 'unselect_all' ]; ?>
				</button>
			</p>
		<?php
	}


	protected function render_competitive_works_fields( &$competitive_works ) {
		//
	}



	/**
	 * Выполняет действие
	 * @return   array    массив "действий"
	 */
	public function add_action( $actions ) {
		$actions[ $this->get_action_name() ] = $this->get_action_label();
		return $actions;
	}



}