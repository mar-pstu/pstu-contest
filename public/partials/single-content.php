<?php

if ( ! defined( 'ABSPATH' ) ) {	exit; };

?>

<table class="competitive-work-single-meta-table">
	<tbody>
		<tr>
			<th><?php _e( 'Рейтинг', $this->plugin_name ); ?></th>
			<td>
				<p><?php echo ( empty( $rating ) ) ? '-' : $rating; ?></p>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Шифр', $this->plugin_name ); ?></th>
			<td>
				<p><?php echo ( empty( $cipher ) ) ? '-' : $cipher; ?></p>
			</td>
		</tr>
		<?php if ( is_array( $universities ) && ! empty( $universities ) ) : ?>
			<tr>
				<th><?php _e( 'Университет', $this->plugin_name ); ?></th>
				<td>
					<p><?php echo wp_sprintf( '%l', $universities ); ?></p>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<th><?php _e( 'Авторы', $this->plugin_name ); ?></th>
			<td>
				<?php

					if ( ( bool ) $show_authors ) {
						if ( empty( $authors ) ) {
							echo '<p>-</p>';
						} else {
							echo '<ul>';
							foreach ( $authors as &$author ) {
								$author = array_merge( [
									'last_name'   => '',
									'first_name'  => '',
									'middle_name' => '',
								], $author );
								echo '<li>' . $author[ 'last_name' ] . ' ' . $author[ 'first_name' ] . ' ' . $author[ 'middle_name' ] . '</li>';
							}
							echo '</ul>';
						}
					} else {
						echo '<p>' . __( 'Авторы скрыты', $this->plugin_name ) . '</p>';
					}

				?>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Статус', $this->plugin_name ); ?></th>
			<td>
				<?php
					if ( null == $work_status ) {
						echo '<p>' . __( 'Неопределён', $this->plugin_name ) . '</p>';
					} else {
						$status_type = get_term_meta( $work_status->term_id, 'status_type', true );
						$indicator = ( empty( $status_type ) ) ? '' : '<span class="status-type-indicator status-type-indicator--' . $status_type . '"></span> ';
						echo '<p><b>' . $indicator . $work_status->name . '</b></p>';
						if ( ! empty( trim( $work_status->description ) ) ) {
							echo '<div class="small">' . $work_status->description . '</div>';
						}
					}
				?>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Файлы конкурсной работы', $this->plugin_name ); ?></th>
			<td>
				<?php
					if ( empty( $work_files ) ) {
						echo '<p>-</p>';
					} else {
						echo '<ul>';
						foreach ( $work_files as $work_file ) {
							echo '<li><a href="' . esc_attr( $work_file ) . '">' . basename( $work_file ) . '</a></li>';
						}
						echo '</ul>';
					}
				?>
			</td>
		</tr>
		<tr>
			<th><?php _e( 'Рецензии', $this->plugin_name ); ?></th>
			<td>
				<?php
					if ( empty( $reviews ) ) {
						echo '<p>-</p>';
					} else {
						echo '<ul>';
						foreach ( $reviews as $review ) {
							echo '<li><a href="' . esc_attr( $review ) . '">' . basename( $review ) . '</a></li>';
						}
						echo '</ul>';
					}
				?>
			</td>
		</tr>
		<?php if ( ! empty( $invite_files ) ) : ?>
			<tr>
				<th><?php _e( 'Приглашение к участию в конференции', $this->plugin_name ); ?></th>
				<td>
					<ul>
						<?php
							foreach ( $invite_files as $invite_file ) {
								echo '<li><a href="' . esc_attr( $invite_file ) . '">' . basename( $invite_file ) . '</a></li>';
							}
						?>
					</ul>
				</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>