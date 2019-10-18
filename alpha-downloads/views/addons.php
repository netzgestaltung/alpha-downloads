<div class="wrap">
	<h1><?php _e( 'Alpha Downloads Add-Ons', 'alpha-downloads' ); ?></h1>
	<div class="alpha-addons-container">
		<?php if ( is_array( $addons ) && isset( $addons[0]->title ) ) : ?>
			<?php foreach ( $addons as $addon ) : ?>
				<div class="item">
					<a href="<?php echo esc_url( $addon->url ); ?>">
						<?php echo $addon->image; ?>
					</a>
					<h3><?php echo $addon->title; ?></h3>
					<p><?php echo $addon->excerpt; ?></p>
					<div class="action">
						<a class="button-secondary" href="<?php echo esc_url( $addon->url ); ?>"><?php _e( 'Get Add-On', 'alpha-downloads' ); ?></a>
					</div>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<p><?php _e( 'Unable to retrieve available add-ons.', 'alpha-downloads' ); ?></p>
		<?php endif; ?>
	</div>
</div>
