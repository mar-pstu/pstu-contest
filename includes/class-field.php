<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


class Field {


	protected $name;


	protected $label;


	protected $required;


	function __construct( $name, $label, $args = array() ) {
		$args = array_merge( array(
			'required'    => false,
		), $args );
		$this->name = $name;
		$this->label = $label;
		$this->required = ( bool ) $args[ 'required' ];
	}


	public function get_name() {
		return $this->name;
	}


	public function get_label() {
		return $this->label;
	}


	public function is_required() {
		return $this->required;
	}


}