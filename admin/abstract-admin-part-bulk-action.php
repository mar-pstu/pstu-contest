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
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->tax_query = ( isset( $_POST[ 'filter' ][ 'tax_query' ] ) ) ? $this->parse_query( $_POST[ 'filter' ][ 'tax_query' ] ): [];
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
					$this->render_filter_fields();
					$competitive_works = $this->get_competitive_works();
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



	public function parse_query( $request ) {
		$result = [];
		if ( is_array( $request ) ) {
			foreach ( $request as $key => &$value ) {
				$value = wp_parse_id_list( $value );
				if ( ! empty( $value ) ) {
					$result[ $key ] = $value;
				}
			}
		} else {
			$result = wp_parse_id_list( $request );
		}
		return $result;
	}


	/**
	 * Выполняет действие
	 * @return   array    массив "действий"
	 */
	public function add_action( $actions ) {
		$actions[ $this->get_action_name() ] = $this->get_action_label();
		return $actions;
	}


	protected function render_submit_action_button( $label = '' ) {
		if ( empty( $label ) ) {
			$label = __( 'Выполнить действие', $this->plugin_name );
		}
		?>
			<label class="button button-primary">
				<input style="display: none;" type="checkbox" name="action" value="<?php echo $this->action_name; ?>" onchange="if ( confirm( '<?php esc_attr_e( 'Вы уверены?', $this->plugin_name ) ?>' ) ) { this.checked=true; this.form.submit(); }">
				<?php echo $label; ?>
			</label>
		<?php
	}


	/**
	 * Формирует html код формы фильтра
	 * @return   string    html код содержимого страницы
	 */
	protected function render_filter_fields() {
		?>
			<h3><?php _e( 'Фильтр', $this->plugin_name ); ?></h3>
				<?php
					foreach ( [ 
						'cw_year'         => __( 'Год проведения', $this->plugin_name ),
						'work_status'     => __( 'Статус работы', $this->plugin_name ),
						'contest_section' => __( 'Секция', $this->plugin_name ),
						'category'        => __( 'Рубрика', $this->plugin_name ),
					 ] as $id => $label ) {
						$terms = get_terms( array(
							'taxonomy'   => $id,
							'hide_empty' => false,
							'fields'     => 'id=>name',
						) );
						if ( is_array( $terms ) && ! empty( $terms ) ) {
							$control = $this->render_dropdown( "filter[tax_query][{$id}]", $terms, array(
								'selected' => ( array_key_exists( $id, $this->tax_query ) ) ? $this->tax_query[ $id ] : [],
								'atts' => [
									'class'    => 'form-control',
									'id'       => $id,
								],
							) );
						} else {
							$control = __( sprintf( 'Заполните таксономию "%s" или обратитесь к администратору сайта.', $label ), $this->plugin_name );
						}
						include dirname( __FILE__ ) . '/partials/post_type-section-field.php';
					}
				?>
			<p class="text-right">
				<button class="button" type="reset" onclick="this.form.reset(); window.location.reload();">
					<?php _e( 'Сбросить фильтр', $this->plugin_name ); ?>
				</button>
				<button class="button button-primary" type="submit">
					<?php _e( 'Применить фильтр', $this->plugin_name ); ?>
				</button>
			</p>
			<br><hr>
		<?php
	}



	protected function get_competitive_works() {
		$competitive_works_args = [
			'numberposts' => -1,
			'orderby'     => 'name',
			'order'       => 'DESC',
			'post_type'   => 'competitive_work',
		];
		if ( ! empty( $this->tax_query ) ) {
			$competitive_works_args[ 'tax_query' ] = [ 'relation' => 'AND' ];
			foreach ( $this->tax_query as $key => $value ) {
				$competitive_works_args[ 'tax_query' ][] = [
					'taxonomy' => $key,
					'field'    => 'term_id',
					'terms'    => $value,
					'operator' => 'AND',
					'include_children' => true,
				];
			}
		}
		$competitive_works = get_posts( $competitive_works_args );
		return ( is_array( $competitive_works ) ) ? $competitive_works : [];
	}


}