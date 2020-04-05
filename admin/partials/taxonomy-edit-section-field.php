<?php

if ( ! defined( 'ABSPATH' ) ) {	exit; };

?>

<tr class="form-field">

	<th scope="row" valign="top">
		<label for="<?php echo esc_attr( $id ); ?>">
			<?php echo $label; ?>
		</label>
	</th>

	<td>
		<?php echo $control; ?>
	</td>

</tr>