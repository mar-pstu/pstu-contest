<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс отвечающий за функциональность админки для
 * таксономий конкурсных работ
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class AdminPartTaxonomy extends AdminPart {


	/**
	 * Идентификатор пользовательской таксономии
	 * @since    2.0.0
	 * @var      string
	 */
	protected $taxonomy_name;


	/**
	 * Возвращает идентификатор пользовательской таксономии
	 * @return   string   идентификатор пользовательской таксономии
	 */
	public function get_taxonomy_name() {
		return $this->taxonomy_name;
	}


	/**
	 *	Регистрация метабокса для добавления
	 *	пользовательской таксономии к посту
	 * @since    2.0.0
	 * @var      string       $post_type
	 */
	public function add_meta_box( $post_type ) {
		global $wp_taxonomies;
		if ( 'competitive_work' == $post_type && isset( $wp_taxonomies[ $this->taxonomy_name ] ) ) {
			add_meta_box(
				$this->taxonomy_name . '_relationship',
				$wp_taxonomies[ $this->taxonomy_name ]->labels->singular_name,
				array( $this, 'render_metabox_content' ),
				$post_type,
				'side',
				'high',
				null
			);
		}
	}



	/**
	 * Прикрепляет / удаляет выбраные термин к посту
	 * @since    2.0.0
	 * @var      int          $post_id
	 */
	public function save_post( $post_id, $post ) {
		if ( ! isset( $_POST[ "{$this->taxonomy_name}_nonce" ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ "{$this->taxonomy_name}_nonce" ], $this->taxonomy_name ) ) { wp_nonce_ays(); return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( 'page' == $_POST[ 'post_type' ] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_nonce_ays();
			return;
		}
		$terms = array();
		if ( isset( $_POST[ $this->taxonomy_name ] ) ) {
			$terms = wp_parse_id_list( $_POST[ $this->taxonomy_name ] );
		}
		// $this->var_dump( $terms );
		wp_set_object_terms( $post_id, $terms, $this->taxonomy_name, false );
	}


	/**
	 * Выводит html-код контента метабокса таксономии при
	 * редактировании поста.
	 * @since    2.0.0
	 * @var      WP_Post       $post
	 */
	public function render_metabox_content( $post ) {
		wp_nonce_field( $this->taxonomy_name, "{$this->taxonomy_name}_nonce" );
		extract( apply_filters( "{$this->plugin_name}_{$this->taxonomy_name}_select_params", [
			'name'  => $this->taxonomy_name,
			'terms' => get_terms( array(
				'taxonomy'   => $this->taxonomy_name,
				'hide_empty' => false,
				'fields'     => 'id=>name',
			) ),
			'args'           => [
				'selected' => wp_get_object_terms( $post->ID, $this->taxonomy_name, array( 'fields' => 'ids' ) ),
				'atts'     => [
					'class'    => 'form-control',
					'id'       => $this->taxonomy_name,
				],
			],
		] ) );
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$id = '';
			$label = '';
			$control = $this->render_dropdown( $name, $terms, $args );
		} else {
			$control = __( 'Заполните таксономию или обратитесь к администратору сайта.', $this->plugin_name );
		}
		include dirname( __FILE__ ) . '/partials/post_type-section-field.php';
	}


	/**
	 * Фильтр, который добавляет вкладку с опциями для текущей таксономии
	 * на страницу настроектплагина
	 * @since    2.0.0
	 * @param    array     $tabs     исходный массив вкладок идентификатор вкладки=>название
	 * @return   array     $tabs     отфильтрованный массив вкладок идентификатор вкладки=>название
	 */
	public function add_settings_tab( $tabs ) {
		global $wp_taxonomies;
		if ( isset( $wp_taxonomies[ $this->taxonomy_name ] ) ) {
			$tabs[ $this->taxonomy_name ] = $wp_taxonomies[ $this->taxonomy_name ]->labels->name;
		}
		return $tabs;
	}


	/**
	 * Выводит html-код формы ввода настроек для таксономии
	 * @param    string    $page_slug    идентификатор страницы настроек
	 */
	public function render_settings_form( string $page_slug ) {
		?>
			<form action="options.php" method="POST">
				<?php
					settings_fields( $this->taxonomy_name );
					do_settings_sections( $this->taxonomy_name );
					submit_button();
				?>
			</form>
		<?php
	}



}