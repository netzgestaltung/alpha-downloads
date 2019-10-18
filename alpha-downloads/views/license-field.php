<input type="text" name="alpha-downloads[<?php echo $key; ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
<?php if ( $active ) : ?>
	<button type="submit" class="alpha-deactivate-license button-secondary"><?php _e( 'Deactivate', 'alpha-downloads' ); ?></button>
	<p class="description"><?php printf( __( 'Your license will expire on %s.', 'alpha-downloads' ), date( 'F jS Y', strtotime( $status->expires ) ) ); ?></p>
<?php endif; ?>
