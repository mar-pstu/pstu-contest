<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функциональность админки для
 * таксономии "Год проведения"
 *
 * @package    pstu_contest
 * @subpackage pstu_contest/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class AdminTaxonomyUniversity extends AdminPartTaxonomy {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->taxonomy_name = 'university';
	}


	/**
	 * Регистрирует стили для админки
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		parent::enqueue_styles();
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/css/select2.min.css', array(), '4.0.12', 'all' );
	}


	/**
	 * Регистрирует скрипты для админки
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js',  array( 'jquery' ), '4.0.12', false );
		wp_add_inline_script( 'select2', "jQuery(document).ready(function() { jQuery('select#{$this->taxonomy_name}').select2();});", 'after' );
	}


}