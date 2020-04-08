<?php


namespace pstu_contest;


/**
 * Файл, который определяет основной класс плагина
 *
 * @link       https://events.pstu.edu/konkurs-energy/
 * @since      2.0.0
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 */

/**
 * Основной класс плагина
 * @since      2.0.0
 * @package    pstu_contest
 * @subpackage pstu_contest/includes
 * @author     Your Name <chomovva@gmail.com>
 */
class Manager {

	/**
	 * Загрузчик, который отвечает за регистрацию всех хуков, фильтров и шорткодов.
	 * @since    2.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Регистрирует хуки, фильтры, шорткоды
	 */
	protected $loader;

	/**
	 * Уникальый идентификаторв плагина
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
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {


		/**
		 * Методы для работы с полями формы
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/trait-controls.php';


		/**
		 * Абстракные классы с общими свойствами и методами для файлов плагина
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
		 * Класс, отвечающий за дополнительные страницы админки плагина
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/abstract-admin-part.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/abstract-admin-bulk-action.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-settings-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-import.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-export.php';

		/**
		 * Класс, отвечающий за групповые действия
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-show-authors.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-status-change.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-edit-reviews.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-edit-work-files.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-edit-invite-files.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-edit-authors.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-edit-rating.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-bulk-action-edit-cipher.php';

		/**
		 * Классы, отвечающие за хуки, фильтры админки для пользовательских типов записи плагина
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/abstract-admin-part-post_type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-competitive_work.php';
		
		/**
		 * Классы, отвечающие за хуки, фильтры админки для пользовательских таксономий плагина
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/abstract-admin-part-taxonomy.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-taxonomy-cw_year.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-taxonomy-work_status.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-taxonomy-contest_section.php';

		/**
		 * Класс, отвечающий за хуки, фильтры публичной части сайта для "обїектов" плагина
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-public-competitive_work.php';

		$this->loader = new Loader();

	}

	/**
	 * Добавлет функциональность для интернационализации.
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new I18n( $this->get_plugin_name() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}


	/**
	 * Регистрирует новые типы постов и таксономии
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
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// страница настроек плагина
		$settings_manager_class = new AdminSettingsManager( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $settings_manager_class, 'add_page' );
		$this->loader->add_action( 'admin_init', $settings_manager_class, 'register_settings', 10, 0 );

		// страница импорта
		$import_class = new AdminImport( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $import_class, 'add_page' );

		// страница экспорта
		$export_class = new AdminExport( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $export_class, 'add_page' );
		
		// страница-менеджер групповых действий
		$bulk_action_manager_class = new AdminBulkActionManager( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $bulk_action_manager_class, 'add_page' );
		$this->loader->add_action( 'current_screen', $bulk_action_manager_class, 'run_action' );
		$this->loader->add_action( 'admin_enqueue_scripts', $bulk_action_manager_class, 'enqueue_styles', 10, 0 );
		
		// групповое действие - изменение статуса показа авторов работ
		$bulk_action_show_author_class = new AdminBulkActionShowAuthors( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_show_author_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_show_author_class->get_action_name(), $bulk_action_show_author_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_show_author_class->get_action_name(), $bulk_action_show_author_class, 'render_subscreen' );
		
		// групповое действие - изменение статуса конкурсной работы
		$bulk_action_status_change_class = new AdminBulkActionStatusChange( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_status_change_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_status_change_class->get_action_name(), $bulk_action_status_change_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_status_change_class->get_action_name(), $bulk_action_status_change_class, 'render_subscreen' );
		
		// групповое действие - редактирвание списка рецензий
		$bulk_action_edit_reviews_class = new AdminBulkActionEditReviews( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_edit_reviews_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_edit_reviews_class->get_action_name(), $bulk_action_edit_reviews_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_edit_reviews_class->get_action_name(), $bulk_action_edit_reviews_class, 'render_subscreen' );

		// групповое действие - редактирвание списка файлов конкурсных работ
		$bulk_action_edit_work_files_class = new AdminBulkActionEditWorkFiles( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_edit_work_files_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_edit_work_files_class->get_action_name(), $bulk_action_edit_work_files_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_edit_work_files_class->get_action_name(), $bulk_action_edit_work_files_class, 'render_subscreen' );
	
		// групповое действие - редактирвание списка файлов приглашений
		$bulk_action_edit_invite_files_class = new AdminBulkActionEditInviteFiles( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_edit_invite_files_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_edit_invite_files_class->get_action_name(), $bulk_action_edit_invite_files_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_edit_invite_files_class->get_action_name(), $bulk_action_edit_invite_files_class, 'render_subscreen' );

		// групповое действие - редактирвание списка авторов конкурсной работы
		$bulk_action_edit_authors_class = new AdminBulkActionEditAuthors( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_edit_authors_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_edit_authors_class->get_action_name(), $bulk_action_edit_authors_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_edit_authors_class->get_action_name(), $bulk_action_edit_authors_class, 'render_subscreen' );

		// групповое действие - редактирвание рейтингов
		$bulk_action_edit_rating_class = new AdminBulkActionEditRating( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_edit_rating_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_edit_rating_class->get_action_name(), $bulk_action_edit_rating_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_edit_rating_class->get_action_name(), $bulk_action_edit_rating_class, 'render_subscreen' );

		// групповое действие - редактирвание шифров
		$bulk_action_edit_cipher_class = new AdminBulkActionEditCipher( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_bulk_action_list', $bulk_action_edit_cipher_class, 'add_action', 5, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-run_' . $bulk_action_edit_cipher_class->get_action_name(), $bulk_action_edit_cipher_class, 'run_action' );
		$this->loader->add_action( $this->get_plugin_name() . '_bulk_action-subscreen_' . $bulk_action_edit_cipher_class->get_action_name(), $bulk_action_edit_cipher_class, 'render_subscreen' );

		// админ-часть типа записи "Конкурсная работа"
		$competitive_work = new AdminCompetitiveWork( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $competitive_work, 'add_meta_box', 10, 1 );
		$this->loader->add_action( 'save_post', $competitive_work, 'save_post', 10, 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $competitive_work, 'enqueue_styles', 10, 0 );
		$this->loader->add_action( 'admin_enqueue_scripts', $competitive_work, 'enqueue_scripts', 10, 0 );
		$this->loader->add_action( 'manage_edit-' . $competitive_work->get_post_type_name() . '_columns', $competitive_work, 'add_custom_columns', 10, 1 );
		$this->loader->add_action( 'manage_posts_custom_column', $competitive_work, 'render_custom_columns', 10, 1 );
		$this->loader->add_action( 'manage_edit-' . $competitive_work->get_post_type_name() . '_sortable_columns', $competitive_work, 'add_custom_sortable_columns', 10, 1 );
		$this->loader->add_action( 'pre_get_posts', $competitive_work, 'request_custom_sortable_columns', 10, 1 );
		// $this->loader->add_action( '', $competitive_work, '', 10, 1 );

		
		// админ-часть таксономии "Год проведения"
		$cw_year_taxonomy_class = new AdminTaxonomyCWYear( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $cw_year_taxonomy_class, 'add_meta_box', 10, 1 );
		$this->loader->add_action( 'save_post', $cw_year_taxonomy_class, 'save_post', 10, 1 );
		
		// админ-часть таксономии "Статус конкурсной работы" 
		$work_status_taxonomy_class = new AdminTaxonomyWorkStatus( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $work_status_taxonomy_class, 'add_meta_box', 10, 1 );
		$this->loader->add_action( 'save_post', $work_status_taxonomy_class, 'save_post', 10, 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $work_status_taxonomy_class, 'enqueue_styles', 10, 0 );
		$this->loader->add_action( 'admin_enqueue_scripts', $work_status_taxonomy_class, 'enqueue_scripts', 10, 0 );
		$this->loader->add_action( $work_status_taxonomy_class->get_taxonomy_name() . '_add_form_fields', $work_status_taxonomy_class, 'add_custom_fields', 10, 1 );
		$this->loader->add_action( $work_status_taxonomy_class->get_taxonomy_name() . '_edit_form_fields', $work_status_taxonomy_class, 'edit_custom_fields', 10, 1 );
		$this->loader->add_action( 'create_' . $work_status_taxonomy_class->get_taxonomy_name(), $work_status_taxonomy_class, 'save_custom_fields', 10, 1 );
		$this->loader->add_action( 'edited_' . $work_status_taxonomy_class->get_taxonomy_name(), $work_status_taxonomy_class, 'save_custom_fields', 10, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_register_settings', $work_status_taxonomy_class, 'register_settings', 10, 1 );
		$this->loader->add_filter( $this->get_plugin_name() . '_settings-tabs', $work_status_taxonomy_class, 'add_settings_tab', 10, 1 );
		
		// админ-часть таксономии "Секция конкурсной работы"
		$contest_section_taxonomy_class = new AdminTaxonomyContestSection( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $contest_section_taxonomy_class, 'add_meta_box', 10, 1 );
		$this->loader->add_action( 'save_post', $contest_section_taxonomy_class, 'save_post', 10, 1 );

	}

	/**
	 * Регистрация хуков и фильтров для публично части плагина
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$competitive_work = new PublicCompetitiveWork( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $competitive_work, 'enqueue_styles', 10, 0 );
		$this->loader->add_action( 'wp_enqueue_scripts', $competitive_work, 'enqueue_scripts', 10, 0 );

	}

	/**
	 * Запск загрузчика для регистрации хукой, фильтров и шорткодов в WordPress
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Возвращает имя плагина используется для уникальной идентификации его в контексте
	 * WordPress и для определения функциональности интернационализации.
	 * @since     2.0.0
	 * @return    string    Идентификатор плагина
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Возвращает ссылку на класс, который управляет хуками с плагином.
	 * @since     2.0.0
	 * @return    Loader    Класс "загрузчик" хуков, фильтров и шорткодов.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Возвращает номер версии плагина. Используется при регистрации файлов
	 * скриптов, стилей и обновлении плагина.
	 * @since     2.0.0
	 * @return    string    Номер текущей версии плагина
	 */
	public function get_version() {
		return $this->version;
	}

}
