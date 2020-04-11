<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность публичной части сайта
 * для пользовательской таксономии "Статус конкурсной работы"
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class PublicTaxonomyWorkStatus extends PublicPartTaxonomy {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->taxonomy_name = 'work_status';
	}


	/**
	 * Фильтр, который добавляе индикатор типа
	 * статуса к заголовку поста
	 * @since  2.0.0
	 * @param  string   $title   заголовок
	 * @param  int|null $post_id идентификтор поста
	 * @return string            заголовок с добавленным индикатором типа статуса
	 */
	public function filter_post_type_title( string $title, int $post_id = null ) {
		if ( null == $post_id ) {
			$post_id = get_the_ID();
		}
		if ( is_object_in_term( get_post_type( $post_id ), $this->taxonomy_name ) ) {
			$term = get_terms( [
				'taxonomy'    => $this->taxonomy_name,
				'hide_empty'  => false,
				'object_ids'  => ( int ) $post_id,
				'number'      => 1,
				'meta_key'    => 'status_type',
			] );
			if ( is_array( $term ) && ! empty( $term ) ) {
				$term = $term[ 0 ];
				$status_type = get_term_meta( $term->term_id, 'status_type', true );
				$title = sprintf(
					'<span class="status-type-indicator status-type-indicator--%1$s" title="%2$s"></span> %3$s',
					esc_attr( $status_type ),
					esc_attr( $term->name ),
					$title
				);
			}
		}
		return $title;
	}


	/**
	 * Регистрирует стили для публичной части сайта
	 * @since  2.0.0
	 */
	public function enqueue_styles() {
		parent::enqueue_styles();
		$options = get_option( $this->taxonomy_name, [] );
		$status_types_styles = [];
		if ( isset( $options[ 'types' ] ) && ! empty( $options[ 'types' ] ) ) {
			foreach ( $options[ 'types' ] as $type ) {
				if ( ! empty( $type[ 'slug' ] ) && ! empty( $type[ 'color' ] ) ) {
					$status_types_styles[ '.status-type-indicator--' . $type[ 'slug' ] ] = [ 'background-color' => $type[ 'color' ] ];
				}
			}
		}
		wp_add_inline_style( $this->plugin_name, $this->css_array_to_css( $status_types_styles ) );
	}


	/**
	 * Конвертирует ассоциативный массив стилей в css-код
	 * @since  2.0.0
	 * @param  array  $rules ассоциатинвный массив стилей
	 * @param  array  $args  дополнительные параметры вывода
	 * @return string        css-код
	 */
	public function css_array_to_css( array $rules, array $args = [] ) {
		$args = array_merge( array(
			'indent'     => 0,
			'container'  => false,
		), $args );
		$css = '';
		$prefix = str_repeat( '  ', $args[ 'indent' ] );
		foreach ($rules as $key => $value ) {
			if ( is_array( $value ) ) {
				$selector = $key;
				$properties = $value;
				$css .= $prefix . "$selector {\n";
				$css .= $prefix . $this->css_array_to_css( $properties, [
					'indent'     => $args[ 'indent' ] + 1,
					'container'  => false,
				] );
				$css .= $prefix . "}\n";
			} else {
				$property = $key;
				$css .= $prefix . "$property: $value;\n";
			}
		}
		return ( $args[ 'container' ] ) ? "\n<style>\n" . $css . "\n</style>\n" : $css;
	}


}