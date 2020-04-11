<?php


namespace pstu_contest;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


get_header();

?>

	<div class="contest-plugin-container">

<?php


if ( have_posts() ) {

	$current_cw_year_slug = get_query_var( 'cw_year' );

	if ( is_post_type_archive() ) {

		$cw_years = get_terms( [
			'taxonomy'    => 'cw_year',
			'hide_empty'  => false,
			'orderby'     => 'name', 
			'order'       => 'DESC',
		] );
		echo '<h1>' . post_type_archive_title( '', false ) . '</h1>';
		if ( is_array( $cw_years ) && ! empty( $cw_years ) ) {
			echo '<ul class="cw-years-tabs">';
			foreach ( $cw_years as $cw_year ) {
				if ( $cw_year->slug == $current_cw_year_slug ) {
					echo '<li class="current-cw-years-tab"><b>' . apply_filters( 'single_term_title', $cw_year->name ) . '</b></li>';
				} else {
					echo '<li><a href="' . get_term_link( $cw_year, 'cw_year' ) . '">' . $cw_year->name . '</a></li>';
				}
			}
			echo "</ul>";
		}

	} else {
		echo '<h1>' . single_term_title( __( 'Конкурсные работы', PSTU_CONTEST_NAME ) . ' ', false ) . '</h1>';
		echo term_description( $current_cw_year_slug, 'cw_year' );
	}


	?>

		<table class="tablesorter">

			<thead>
				<tr>
					<th><?php _e( 'Шифр', PSTU_CONTEST_NAME ); ?></th>
					<th><?php _e( 'Рейтинг', PSTU_CONTEST_NAME ); ?></th>
					<th><?php _e( 'Название', PSTU_CONTEST_NAME ); ?></th>
					<th><?php _e( 'Секция', PSTU_CONTEST_NAME ); ?></th>
					<th><?php _e( 'Авторы', PSTU_CONTEST_NAME ); ?></th>
					<th><?php _e( 'Статус', PSTU_CONTEST_NAME ); ?></th>
				</tr>
			</thead>

			<tbody>

	<?php

		while ( have_posts() ) {
			
			the_post();

			$contest_sections = get_the_terms( get_the_ID(), 'contest_section' );
			$work_statuses = get_the_terms( get_the_ID(), 'work_status' );
			$authors = ( get_post_meta( get_the_ID(), 'show_authors', true ) ) ? get_post_meta( get_the_ID(), 'authors', true ) : false;

			?>

				<tr>
					<td><?php echo get_post_meta( get_the_ID(), 'cipher', true ); ?></td>
					<td><?php echo get_post_meta( get_the_ID(), 'rating', true ); ?></td>
					<td><a href="<?php the_permalink(); ?>"><?php the_title( '', '', true ); ?></a></td>
					<td>
						<?php
							if ( is_array( $contest_sections ) ) {
								echo wp_sprintf( '%l', wp_list_pluck( $contest_sections, 'name', null ) );
							}
						?>
					</td>
					<td>
						<?php
							if ( is_array( $authors ) && ! empty( $authors ) ) {
								echo '<ul>' . implode( "\r\n", array_map( function ( $author ) {
									echo '<li>' . $author[ 'first_name' ] . ' ' . $author[ 'last_name' ] . ' ' . $author[ 'middle_name' ] . '</li>';
								}, $authors ) ) . '</ul>';
							} else {
								echo '-';
							}
						?>
					</td>
					<td>
						<?php
							if ( is_array( $work_statuses ) ) {
								echo wp_sprintf( '%l', wp_list_pluck( $work_statuses, 'name', null ) );
							}
						?>
					</td>
				</tr>

			<?php

		}

	?>

			</tbody>
		</table>

	<?php

} else {
	
	?>

		<p><?php _e( 'Конкурсные работы не найдены', PSTU_CONTEST_NAME ); ?></p>

	<?php

}


?>

	</div>

<?php

get_footer();