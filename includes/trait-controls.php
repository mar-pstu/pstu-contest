<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


trait Controls {


	function objects_to_choices( $type, $list ) {
		$choices = array();
		if ( is_array( $list ) && ! empty( $list ) ) {
			if ( 'post_type' == $type ) {
				$choices = wp_list_pluck( $list, 'post_title', 'id' );
			} elseif ( 'taxonomy' == $type ) {
				$choices = wp_list_pluck( $list, 'name', 'term_id' );
			}
		}
		return $choices;
	}


	function render_atts( $atts ) {
		$html = __return_empty_string();
		if ( ! empty( $atts ) ) {
			foreach ( $atts as $key => $value ) {
				$html .= ' ' . $key . '="' . $value . '"';
			}
		}
		return $html;
	}



	/**
	 * Генерирует html-код простого элемента управления
	 * 
	 * @var    string     $name     имя єлемента
	 * @var    string     $type     тип
	 * @var    array      $atts     дополнительные параметры
	 */
	function render_input( $name, $type="text", $atts = array() ) {
		$atts[ 'name' ] = $name;
		$atts[ 'type' ] = ( in_array( $type, array( 'number', 'email', 'password', 'hidden', 'date', 'datetime', 'checkbox', 'radio' ) ) ) ? $type : 'text';
		return '<input ' . $this->render_atts( $atts ) . ' >';
	}



	function render_dropdown( $name, $choices, $args = array() ) {
		$args = array_merge( [
			'selected'          => [],
			'echo'              => false,
			'show_option_none'  => '-',
			'option_none_value' => '',
			'atts'              => [
				'id'                => $name,
				'class'             => 'form-control',
			]
		], $args );
		$args[ 'atts' ][ 'name' ] = $name;
		$output = __return_empty_array();
		if ( is_array( $choices ) && ! empty( $choices ) ) {
			if ( ! is_array( $args[ 'selected' ] ) ) {
				$args[ 'selected' ] = [ $args[ 'selected' ] ];
			}
			if ( $args[ 'show_option_none' ] ) {
				$output[] = sprintf( '<option value="%1$s">%2$s</option>', esc_attr( $args[ 'option_none_value' ] ), $args[ 'show_option_none' ] );
			}
			foreach ( $choices as $value => $label ) {
				$selected = selected( true, in_array( $value, $args[ 'selected' ] ), false );
				$output[] = sprintf( '<option value="%1$s" %2$s>%3$s</option>', $value, $selected, $label );
			}
		}
		// $this->var_dump( $args );
		$html = ( empty( $output ) ) ? '' : sprintf(
			'<select %1$s>%2$s</select>',
			$this->render_atts( $args[ 'atts' ] ),
			implode( "\r\n", $output )
		);
		if ( $args[ 'echo' ] ) {
			echo $html;
		}
		return $html;
	}


	/**
	 * Создаёт редактируемый список полей
	 *
	 * @var   string    $name     имя поля
	 * @var   array     $value    значение полей
	 * @var   array()   $args     дополниьельные параметры
	 */
	function render_list_of_templates( $name, $value, $args = array() ) {
		$html = '';
		$args = array_merge( array(
			'template' => $this->render_input( $name ),
		), $args );
		if ( ! is_array( $value ) ) {
			$value = wp_parse_list( $value );
		}
		if ( empty( $value ) ) {
			$data = '[]';
		} else {
			// if ( ! ( bool ) ( count( $value ) - count( $value, COUNT_RECURSIVE ) ) ) {
				$value = array_map( function ( $item ) {
					return array( 'value' => $item );
				}, $value );
			// }
			$data = wp_json_encode( $value );
		}
		if ( ! empty( trim( $args[ 'template' ] ) ) ) {;
			ob_start();
			?>
				<div class="list-of-templates" data-list-of-templates="<?php echo $name; ?>" >
					<script type="text/javascript">
						var <?php echo $name; ?>_data = <?php echo $data; ?>;
					</script>
					<div class="list"></div>
					<button  class="button button-primary add-button" type="button"><?php _e( 'Добавить строку', $this->plugin_name ); ?></button>
					<script type="text/html" id="tmpl-<?php echo $name; ?>">
						<div class="list-item">
							<div class="template">
								<?php echo $args[ 'template' ]; ?>	
							</div>
							<button type="button" class="button remove-button">&times;</button>
						</div>
					</script>
				</div>
			<?
			$html = ob_get_contents();
			ob_end_clean();
		}
		return $html;
	}



	/**
	 * Создаёт поле выбора файла из галереи WordPress
	 *
	 * @var   string    $name     имя поля
	 * @var   array     $value    значение полей
	 */
	function render_file_choice( $name, $files, $atts = array() ) {
		$atts = array_merge( array(
			'name'   => '',
			'class'  => '',
		), $atts );
		$atts[ 'name' ] = $name;
		$atts[ 'class' ] = $atts[ 'class' ] . ' button file-choice-control';
		$atts[ 'type' ] = 'text';
		ob_start();
		?>
			<div class="file-choice-field">
				<input <?php echo $this->render_atts( $atts ); ?> >
				<button type="button" class="button button-primary file-choice-button">+</button>
			</div>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}



	/**
	 * Создаёт сборное поле
	 *
	 * @var   string     $controls    Массив сгенерированных полей
	 */
	function render_composite_field( string ... $controls ) {
		return ( func_num_args() > 0 ) ? '<div class="composite-field">' . implode( "\r\n", $controls ) . '</div>' : '';
	}


	/**
	 * Создаёт элемент управления типа флажек
	 *
	 * @var   string    $name     имя поля
	 * @var   string    $value    значение полей
	 * @var   string    $label    метлка-описание поля
	 * @var   array     $atts     аттрибуты тега
	 */
	function render_checkbox( $name, $value, $label, $atts = array() ) {
		$atts[ 'value' ] = $value;
		return sprintf(
			'<label class="checkbox">%1$s %2$s</label>',
			$this->render_input( $name, 'checkbox', $atts ),
			$label
		);
	}


	/**
	 * Создаёт элемент управления типа радиокнопка
	 *
	 * @var   string    $name     имя поля
	 * @var   string    $value    значение полей
	 * @var   string    $label    метлка-описание поля
	 * @var   array     $args     дополниьельные параметры
	 */
	function render_radio_button( $name, $value, $label, $args = array() ) {
		$args[ 'value' ] = 'value';
		return sprintf(
			'<label class="radio">%1$s %2$s</label>',
			$this->render_input( $name, 'radio', $args ),
			$label
		);
	}


	/**
	 * Создаёт список флажков
	 *
	 * @var   string    $name        имя поля
	 * @var   array     $controls    список флажков
	 * @var   array     $args        дополниьельные параметры
	 */
	function render_list_of_checkboxes( $name, $choices, $args ) {
		return '';
	}


	/**
	 * Создаёт список радиокнопок
	 *
	 * @var   string    $name        имя поля
	 * @var   array     $controls    список радиокнопок
	 * @var   array     $args        дополниьельные параметры
	 */
	function render_list_of_radio_buttons( $name, $choices, $args ) {
		return '';
	}


	/**
	 * Очищает массив со строками ссылками
	 *
	 * @since    2.0.0
	 * @var      array   Ожидает неочищенный массив
	 * @return   array   Возвращает очищенный массив url
	 */
	public function sanitize_url_list( $value ) {
		$result = array();
		if ( is_array( $value ) ) {
			foreach ( $value as $url ) {
				if ( ( bool ) preg_match( '~^(?:https?://)?[^.]+\.\S{2,4}$~iu', $url ) ) {
					$result[] = trim( $url );
				}
			}
		}
		return $result;
	}



	public function sanitize_person_data( $value ) {
		$result = [];
		if ( is_array( $value ) && ! empty( $value ) ) {
			foreach ( $value as &$person ) {
				if ( is_array( $person ) && ! empty( $person ) ) {
					$person = $this->parse_only_allowed_args( [
						'last_name'   => '',
						'first_name'  => '',
						'middle_name' => '',
					], $person, [
						'sanitize_text_field',
						'sanitize_text_field',
						'sanitize_text_field',
					], [
						'last_name', 'first_name'
					] );
					if ( $person && ! empty( $person[ 'last_name' ] ) && ! empty( $person[ 'first_name' ] ) ) {
						$result[] = $person;
					}
				}
			}
		}
		return $result;
	}



	public function parse_persons_from_string( $value ) {
		$persons = [];
		if ( ! is_array( $value ) ) {
			$value = explode( ",", $value );
		}
		foreach ( $value as &$item ) {
			$item = explode( " ", $item );
			$item = array_filter( $item, function( $element ) {
				return ! empty( trim( $element ) );
			} );
			if ( ! empty( $item ) ) {
				$person = [];
				if ( count( $item ) > 3 ) {
					$person[ 'middle_name' ] = sanitize_text_field( array_pop( $item ) );
					$person[ 'first_name' ] = sanitize_text_field( array_pop( $item ) );
					$person[ 'last_name' ] = sanitize_text_field( trim( implode( " ", $item ) ) );
				} else {
					$count = 0;
					foreach ( [ 'last_name', 'first_name', 'middle_name' ] as $key ) {
						$person[ $key ] = ( isset( $item[ $count ] ) ) ? sanitize_text_field( $item[ $count ] ) : '';
						$count++;
					}
				}
				if ( ! empty( $person ) ) {
					$persons[] = $person;
				}
			}
		}
		return $persons;
	}



	/**
	 * Функция для очистки массива параметров
	 * @param  array $default           расзерённые парметры и стандартные значения
	 * @param  array $args              неочищенные параметры
	 * @param  array $sanitize_callback одномерный массив с именами функция, с помощью поторых нужно очистить параметры
	 * @param  array $required          обязательные параметры
	 * @return array                    возвращает ощиченный массив разрешённых параметров
	 */
	public function parse_only_allowed_args( $default, $args, $sanitize_callback = [], $required = [] ) {
		$args = ( array ) $args;
		$result = [];
		$count = 0;
		while ( ( $value = current( $default ) ) !== false ) {
			$key = key( $default );
			if ( array_key_exists( $key, $args ) ) {
				$result[ $key ] = $args[ $key ];
				if ( isset( $sanitize_callback[ $count ] ) && ! empty( $sanitize_callback[ $count ] ) ) {
					$result[ $key ] = $sanitize_callback[ $count ]( $result[ $key ] );
				}
			} elseif ( in_array( $key, $required ) ) {
				return null;
			} else {
				$result[ $key ] = $value;
			}
			$count = $count + 1;
			next( $default );
		}
		return $result;
	}


}