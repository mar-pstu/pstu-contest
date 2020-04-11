<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность публичной части сайта
 * для пользовательской таксономии "Год проведения"
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class PublicTaxonomyCWYear extends PublicPartTaxonomy {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->taxonomy_name = 'cw_year';
	}


	/**
	 * Добавляет к запросу выборку по таксономии "Год проведения"
	 * в случае если её небыло и это страница архива
	 * @param WP_Query $query запрос
	 */
	public function set_term_query( $query ) {
		$options = get_option( $this->taxonomy_name );
		if (
			isset( $options[ 'current_year' ] )
			 && ! empty( $options[ 'current_year' ] )
			 && $query->is_main_query()
			 && is_object_in_taxonomy( $query->get( 'post_type' ), $this->taxonomy_name )
			 && ! is_tax( $this->taxonomy_name )
		) {
			$current_year_slug = get_term_field( 'slug', $options[ 'current_year' ], $this->taxonomy_name, 'db' );
			if ( ! empty( $current_year_slug ) ) {
				$query->set( $this->taxonomy_name, $current_year_slug );
			}
		}
	}

}