<?php


namespace pstu_contest;


global $wp_query;

global $post;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


get_header();


?>

	<div class="contest-plugin-container">

<?php

$current_cw_year_slug = get_query_var( 'cw_year' );
$current_cw_year = get_term_by( 'slug', $current_cw_year_slug, 'cw_year', OBJECT, 'raw' );
$description = '';
$cw_year_name = '';

if ( $current_cw_year && ! is_wp_error( $current_cw_year ) ) {
	$description = $current_cw_year->description;
	$cw_year_name = $current_cw_year->name;
}

$options = get_option( 'competitive_work', [] );
if ( ! is_array( $options ) ) {
	$options = [];
}
if ( ! isset( $options[ 'table_style' ] ) || empty( $options[ 'table_style' ] ) ) {
	$options[ 'table_style' ] = 'default';
}

echo '<h1>' . __( 'Конкурсные работы', PSTU_CONTEST_NAME ) . ' ' . $cw_year_name . '</h1>';

$cw_years = get_terms( [
	'taxonomy'    => 'cw_year',
	'hide_empty'  => false,
	'orderby'     => 'name', 
	'order'       => 'DESC',
] );

if ( is_array( $cw_years ) && ! empty( $cw_years ) ) {
	echo '<ul class="cw-years-tabs wp-clearfix">';
	foreach ( $cw_years as $cw_year ) {
		if ( $cw_year->slug == $current_cw_year_slug ) {
			echo '<li class="current"><b>' . apply_filters( 'single_term_title', $cw_year->name ) . '</b></li>';
		} else {
			echo '<li><a href="' . get_term_link( $cw_year, 'cw_year' ) . '">' . $cw_year->name . '</a></li>';
		}
	}
	echo "</ul>";

	echo $description;

}




$contest_sections = get_terms( [
	'taxonomy'   => 'contest_section',
	'hide_empty' => false,
], '' );


if ( have_posts() && is_array( $contest_sections ) && ! empty( $contest_sections ) ) {

	foreach ( $contest_sections as $contest_section ) {
		
		$objects_in_term = get_objects_in_term( $contest_section->term_id, $contest_section->taxonomy, [] );

		if ( is_array( $objects_in_term ) && ! empty( $objects_in_term ) ) {

			$entries = array_filter( $wp_query->posts, function ( $entry ) use ( $objects_in_term ) {
				return in_array( $entry->ID, $objects_in_term );
			}, 0 );

			if ( ! empty( $entries ) ) {

				?>

					<h2><?php printf( __( 'Секція: «%s»', PSTU_CONTEST_NAME ), apply_filters( 'single_term_title', $contest_section->name ) ); ?></h2>

					<?php if ( ! empty( trim( $contest_section->description ) ) ) : ?>
						<div class="contest_section-<?php echo $contest_section->term_id; ?>">
							<?php echo $contest_section->description; ?>
						</div>
					<?php endif; ?>

					<p><small><?php printf( __( 'Знайдено робіт / всього: <b>%s / %s</b>', PSTU_CONTEST_NAME ), count( $entries ), count( $wp_query->posts ) ); ?></small></p>

					<table class="tablesorter tablesorter-<?php echo esc_attr( $options[ 'table_style' ] ); ?>">

						<thead>
							<tr>
								<th><?php _e( 'Шифр', PSTU_CONTEST_NAME ); ?></th>
								<th><?php _e( 'Рейтинг', PSTU_CONTEST_NAME ); ?></th>
								<th><?php _e( 'Название', PSTU_CONTEST_NAME ); ?></th>
								<th><?php _e( 'Авторы', PSTU_CONTEST_NAME ); ?></th>
								<th><?php _e( 'Статус', PSTU_CONTEST_NAME ); ?></th>
							</tr>
						</thead>

						<tbody>

				<?php

					foreach ( $entries as $entry ) {

						$post = $entry;

						setup_postdata( $post );

						$work_statuses = get_the_terms( get_the_ID(), 'work_status' );
						$authors = ( get_post_meta( get_the_ID(), 'show_authors', true ) ) ? get_post_meta( get_the_ID(), 'authors', true ) : false;

						?>

							<tr>
								<td><?php echo get_post_meta( get_the_ID(), 'cipher', true ); ?></td>
								<td><?php echo get_post_meta( get_the_ID(), 'rating', true ); ?></td>
								<td><a href="<?php the_permalink(); ?>"><?php the_title( '', '', true ); ?></a></td>
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

					wp_reset_postdata();

				?>

						</tbody>
					</table>

				<?php

			}

		}

	}

} else {

	?>

		<p><?php _e( 'Секції на знайдені!', PSTU_CONTEST_NAME ); ?></p>

	<?php

}

?>

	</div>

<?php

get_footer();