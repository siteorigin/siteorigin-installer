<span
	class="button-secondary siteorigin-installer"
	data-type="<?php echo esc_attr( $item['type'] ); ?>"
	data-slug="<?php echo esc_attr( $slug ); ?>"
	data-status="<?php echo esc_attr( $status ); ?>"
	data-version="<?php echo esc_attr( $version ); ?>"
	<?php if ( empty( $status ) ) { ?>
		style="display: none;"
	<?php } ?>
>
	<?php echo $text; ?>
</span>
