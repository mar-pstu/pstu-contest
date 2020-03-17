<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Регистрация всех хуков и шорткодов.
 *
 * Сохраняет коллекцию всех хуков и шорткодов. После вызова функции run запускает их.
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class Loader {


	/**
	 * Массив хуков зарегистрирвоанных в WordPress
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      array    $actions    Хуки, зарегистрированные в WordPress, запускаются при загрузке плагина.
	 */
	protected $actions;


	/**
	 * Массив фильтров зарегистрирвоанных в WordPress
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      array    $filters    Фильтры, зарегистрированные в WordPress, запускаются при загрузке плагина.
	 */
	protected $filters;


	/**
	 * Массив шорткодов
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      array    $shortcode    Массив зарегистрированных шорткодов.
	 */
	protected $shortcodes ;


	/**
	 * Инициализирует коллекции, используемые для хуков, фильтров и шорткодов.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();
		$this->shortcodes = array();

	}


	/**
	 * Добавление нового действия в коллекцию для регистрации в WordPress.
	 *
	 * @since    2.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}


	/**
	 * Добавление нового фильтра в коллекцию для регистрации в WordPress.
	 *
	 * @since    2.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}


	/**
	 * Добавляет новый шорткод в массив для регистрации в Wordpress
	 *
	 * @since    2.0.0
	 * @param    string               $hook             Имя фильтра WordPress, который регистрируется.
	 * @param    object               $component        Ссылка на экземпляр объекта, для которого определен фильтр.
	 * @param    string               $callback         Имя метода в $component.
	 */
	public function add_shortcode( $hook, $component, $callback ) {
		$this->shortcodes = $this->add( $this->shortcodes, $hook, $component, $callback, null, null );
	}


	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook[ 'hook' ], array( $hook[ 'component' ], $hook[ 'callback' ] ), $hook[ 'priority' ], $hook[ 'accepted_args' ] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook[ 'hook' ], array( $hook[ 'component' ], $hook[ 'callback' ] ), $hook[ 'priority' ], $hook[ 'accepted_args' ] );
		}

		foreach ( $this->shortcodes as $hook ) {
			add_shortcode( $hook[ 'hook' ], array( $hook[ 'component' ], $hook[ 'callback' ] ) );
			add_shortcode( mb_strtoupper( $hook[ 'hook' ] ), array( $hook[ 'component' ], $hook[ 'callback' ] ) );
		}

	}

}
