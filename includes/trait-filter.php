<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


trait Filter {


	/**
	 * Разбирает запрос фильтра для передачи в WP_Query
	 * @param  array $request запрос
	 * @return array          результат
	 */
	public function parse_tax_query( array $request ) {
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
	 * Разбирает запрос фильтра для передачи в WP_Query
	 * @param  array $request запрос
	 * @return array          результат
	 */
	public function parse_custom_query( array $request ) {
		$result = [];
		if ( is_array( $request ) ) {
			foreach ( $request as $key => &$value ) {
				$value = sanitize_text_field( $value );
				if ( ! empty( $value ) ) {
					$result[ $key ] = $value;
				}
			}
		} else {
			$result = sanitize_text_field( $request );
		}
		return $result;
	}


	/**
	 * Формирует html код формы фильтра
	 * @param    string    $path          путь к папке с файлом класса
	 * @param    array     $tax_query    выборка по таксономиям
	 * @return   string    html          код содержимого страницы
	 */
	protected function render_filter_fields( string $path, array $tax_query = [] ) {
		?>
			<h3><?php _e( 'Фильтр', $this->plugin_name ); ?></h3>
			<?php
				do_action( "{$this->plugin_name}_filter_fileds_before", $path, $tax_query );
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
							'selected' => ( array_key_exists( $id, $tax_query ) ) ? $tax_query[ $id ] : [],
							'atts' => [
								'class'    => 'form-control',
								'id'       => $id,
							],
						) );
					} else {
						$control = __( sprintf( 'Заполните таксономию "%s" или обратитесь к администратору сайта.', $label ), $this->plugin_name );
					}
					include $path . '/partials/form-group.php';
				}
				do_action( "{$this->plugin_name}_filter_fileds_after", $path, $tax_query );
			?>
			<p class="text-right">
				<button class="button" type="reset" onclick="this.form.reset(); window.location.reload();">
					<?php _e( 'Сбросить фильтр', $this->plugin_name ); ?>
				</button>
				<?php $this->render_submit_action_button( 'filter', __( 'Применить фильтр', $this->plugin_name ), true ); ?>
			</p>
			<br><hr>
		<?php
	}



	/**
	 * Формирует запрос на выборку конкурсных работ
	 * @param  array  $tax_query выборка по таксономиям
	 * @return array             конкурсные работы
	 */
	protected function get_competitive_works( array $tax_query = [] ) {
		$competitive_works_args = [
			'numberposts' => -1,
			'orderby'     => 'name',
			'order'       => 'DESC',
			'post_type'   => 'competitive_work',
			'meta_query'  => [
				'relation'  => 'OR',
			]
		];
		if ( ! empty( $tax_query ) ) {
			$competitive_works_args[ 'tax_query' ] = [ 'relation' => 'AND' ];
			foreach ( $tax_query as $key => $value ) {
				$competitive_works_args[ 'tax_query' ][] = [
					'taxonomy' => $key,
					'field'    => 'term_id',
					'terms'    => $value,
					'operator' => 'AND',
					'include_children' => true,
				];
			}
		}
		$competitive_works = get_posts( apply_filters( "{$this->plugin_name}_filter_result_args", $competitive_works_args ) );
		return ( is_array( $competitive_works ) ) ? $competitive_works : [];
	}


	/**
	 * Формирует код кнопки для выполнеия действия
	 * @param  string $action идентификатор действия
	 * @param  string $label  текст кнопки обёртки
	 * @return string         html код кнопки
	 */
	protected function render_submit_action_button( string $action_name = 'run', string $label = '', $echo = true ) {
		if ( empty( $label ) ) {
			$label = __( 'Выполнить действие', $this->plugin_name );
		}
		$question = esc_attr__( 'Вы уверены?', $this->plugin_name );
		$html = '<label class="button button-primary">' . $this->render_input( 'action', 'radio', [
			'style'    => 'display: none;',
			'value'    => $action_name,
			'id'       => '',
			'onchange' => "if ( confirm( '{$question}' ) ) { this.checked=true; this.form.submit(); } else { this.checked=false; }"
		] ) . $label . '</label>';
		if ( $echo ) {
			echo $html;
		}
		return $html;
	}


}