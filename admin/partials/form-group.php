<?php

if ( ! defined( 'ABSPATH' ) ) {	exit; };

?>

<div class="form-group">

	<?php if ( ! empty( $label ) ) : ?>
		<label class="form-label" for="<?php echo esc_attr( $id ); ?>">
			<?php echo $label; ?>
		</label>
	<?php endif; ?>

	<?php echo $control; ?>

</div>