<?php


namespace pstu_contest;


/**
 * Файл, который определяет основной класс плагина
 *
 * @link       http://example.com
 * @since      2.0.0
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 */

/**
 * Основной класс плагина
 *
 * @since      2.0.0
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     Your Name <chomovva@gmail.com>
 */
class Manager {

	/**
	 * Загрузчик, который отвечает за регистрацию всех хуков, фильтров и шорткодов.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Регистрирует хуки, фильтры, шорткоды
	 */
	protected $loader;

	/**
	 * Уникальый идентификаторв плагина
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $plugin_name    Строка используется для идентификации плагина в Wp и интернационализации
	 */
	protected $plugin_name;

	/**
	 * Текущая версия плагина
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $version    Текущая версия плагина
	 */
	protected $version;

	/**
	 * Инициализация переменных плагина, подключение файлов.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {
		$this->version = ( defined( 'PSTU_CONTEST_VERSION' ) ) ? PSTU_CONTEST_VERSION : '2.0.0';
		$this->plugin_name = ( defined( 'PSTU_CONTEST_NAME' ) ) ? PSTU_CONTEST_NAME : 'pstu_contest';
		$this->load_dependencies();
		$this->set_locale();
		$this->init();
		if ( is_admin() && ! wp_doing_ajax() ) {
			$this->define_admin_hooks();
		} else {
			$this->define_public_hooks();
		}
	}

	/**
	 * Подключает файлы с "зависимостями"
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {


		/**
		 * Методы для работы с полями формы
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/trait-controls.php';


		/**
		 * Абстракный класс с общими свойствами и методами для файлов плагина
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/abstract-part.php';

		/**
		 * Класс для создания метаполя
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-field.php';

		/**
		 * Класс, отвечающий за регистрацию хуков, фильтров и шорткодов.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';

		/**
		 * Класс отвечающий за интернализацию.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-i18n.php';

		/**
		 * Касс, который регистрирует типов записей и таксономий.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-init.php';

		/**
		 * Класс, отвечающий за страницу настроек плагина
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-settings.php';

		/**
		 * Класс, отвечающий за групповые действия
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/abstract-admin-bulk-action.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-show-authors.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-status-change.php';

		/**
		 * Класс, отвечающий за хуки, фильтры админки для "Конкурсных работ"
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-competitive_work.php';

		/**
		 * Класс, отвечающий за хуки, фильтры админки для таксономии "Год проведения"
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-cw_year.php';

		/**
		 * Класс, отвечающий за хуки, фильтры админки для таксономии "Статус работы"
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-work_status.php';

		/**
		 * Класс, отвечающий за хуки, фильтры админки для таксономии "Секция работы"
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-contest_section.php';

		/**
		 * Класс, отвечающий за хуки, фильтры публичной для "Конкурсных работ"
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-public-competitive_work.php';

		$this->loader = new Loader();

	}

	/**
	 * Добавлет функциональность для интернационализации.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new I18n( $this->get_plugin_name() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}


	/**
	 * Регистрирует новые типы постов и таксономии
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function init() {
		$plugin_register_objects = new Init( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $plugin_register_objects, 'register_post_types' );
		$this->loader->add_action( 'init', $plugin_register_objects, 'register_taxonomies' );
		$this->loader->add_filter( $this->get_plugin_name() . '_get_fields', $plugin_register_objects, 'get_fields', 10, 1 );
	}


	/**
	 * Регистрация хуков и фильтров для админ части плагина
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$settings = new AdminSettings( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $settings, 'add_page' );
		$this->loader->add_action( 'admin_init', $settings, 'register_settings' );
		$this->loader->add_action( 'admin_enqueue_scripts', $settings, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $settings, 'enqueue_scripts' );
		$bulk_action = new AdminBulkActionManager( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $bulk_action, 'add_page' );
		$this->loader->add_action( 'current_screen', $bulk_action, 'run_action' );
		$bulk_action_show_author_class = new AdminBulkActionShowAuthors( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_show_author_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_show_author_class->get_action_name(), $bulk_action_show_author_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_show_author_class->get_action_name(), $bulk_action_show_author_class, 'render_subscreen' );
		$this->loader->add_action( 'admin_enqueue_scripts', $bulk_action_show_author_class, 'enqueue_styles' );
		$bulk_action_status_change_class = new AdminBulkActionStatusChange( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_status_change_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_status_change_class->get_action_name(), $bulk_action_status_change_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_status_change_class->get_action_name(), $bulk_action_status_change_class, 'render_subscreen' );
		$this->loader->add_action( 'admin_enqueue_scripts', $bulk_action_status_change_class, 'enqueue_styles' );
		$competitive_work = new AdminCompetitiveWork( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $competitive_work, 'add_meta_box' );
		$this->loader->add_action( 'save_post', $competitive_work, 'save_post' );
		$this->loader->add_action( 'admin_enqueue_scripts', $competitive_work, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $competitive_work, 'enqueue_scripts' );
		$cw_year = new AdminCWYear( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $cw_year, 'add_meta_box' );
		$this->loader->add_action( 'save_post', $cw_year, 'save_post' );
		$work_status = new AdminWorkStatus( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $work_status, 'add_meta_box' );
		$this->loader->add_action( 'save_post', $work_status, 'save_post' );
		$contest_section = new AdminContestSection( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $contest_section, 'add_meta_box' );
		$this->loader->add_action( 'save_post', $contest_section, 'save_post' );
	}

	/**
	 * Регистрация хуков и фильтров для публично части плагина
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$competitive_work = new PublicCompetitiveWork( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $competitive_work, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $competitive_work, 'enqueue_scripts' );

	}

	/**
	 * Запск загрузчика для регистрации хукой, фильтров и шорткодов в WordPress
	 *
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Возвращает имя плагина используется для уникальной идентификации его в контексте
	 * WordPress и для определения функциональности интернационализации.
	 *
	 * @since     2.0.0
	 * @return    string    Идентификатор плагина
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Возвращает ссылку на класс, который управляет хуками с плагином.
	 *
	 * @since     2.0.0
	 * @return    Loader    Класс "загрузчик" хуков, фильтров и шорткодов.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Возвращает номер версии плагина. Используется при регистрации файлов
	 * скриптов, стилей и обновлении плагина.
	 *
	 * @since     2.0.0
	 * @return    string    Номер текущей версии плагина
	 */
	public function get_version() {
		return $this->version;
	}

}
