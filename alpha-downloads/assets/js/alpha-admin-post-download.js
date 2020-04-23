jQuery( document ).ready( function( $ ){

	// Main Add/Edit Download Screen
	ALPHA_Admin_Download = {

		options: {},

		init: function( options ) {
			this.options = options;
			this.eventListeners();
			this.updateStatus();
		},

		eventListeners: function() {
			var self = this;

			// Delete file
			$( '.alpha-delete-file' ).on( 'click', function( e ) {
				self.deleteFile();
				e.preventDefault();
			} );

			// Members redirect
			$( document ).on( 'change', '[name="members_only"]', function( e ) {
				if ( 1 == $( this ).val() || '' == $( this ).val() ) {
					$( '#members_only_sub' ).show();
				}
				else {
					$( '#members_only_sub' ).hide();
				}
			} );
		},

		addFile: function( url ) {
			$( '#alpha-file-url' ).val( url );

			// Set default states
			$( '.file-icon img' ).attr( 'src', this.options.default_icon );
			$( '.file-name' ).html( '--' );
			$( '.file-size' ).html( '--' );

			this.updateStatus();
			this.toggleViews();
		},

		deleteFile: function() {
			$( '#alpha-file-url' ).val( '' );
			this.updateStatus();
			this.toggleViews();
		},

		toggleViews: function() {
			if ( '' != $( '#alpha-file-url' ).val() ) {
				// Fade add/existing view
				$( '#alpha-new-download' ).hide( 0, function() {
					// Show existing view
					$( '#alpha-existing-download' ).show();
				} );
			}
			else {
				$( '#alpha-existing-download' ).hide( 0, function() {
					// Show new view
					$( '#alpha-new-download' ).show();
				} );
			}
		},

		updateStatus: function() {
			var self = this;
			var url = $( '#alpha-file-url' ).val();

			// Show loading spinner
			$( '.file-status .status' ).removeClass( 'local remote warning' ).addClass( 'spinner' );

			$.ajax( {

				url: self.options.ajaxURL,

				data: {
					action: self.options.action,
					nonce: self.options.nonce,
					url: url
				},

				dataType: 'json',

				success: function( response ) {
					$( '.file-name' ).html( response.content.filename );

					if ( 'success' == response.status ) {
						// Update file details
						$( '.file-icon img' ).attr( 'src', response.content.icon );
						$( '.file-size' ).html( response.content.size );
						$( '.file-status .status' ).removeClass( 'spinner' ).addClass( response.content.type );

						// Add title
						if ( 'local' == response.content.type ) {
							$( '.file-status .status' ).attr( 'title', self.options.lang_local );
						}

						if ( 'remote' == response.content.type ) {
							$( '.file-status .status' ).attr( 'title', self.options.lang_remote );
						}
					}
					else {
						// Change status icon
						$( '.file-status .status' ).removeClass( 'spinner' ).addClass( 'warning' ).attr( 'title', self.options.lang_warning );
					}
				}

			} );
		}

	};

	// Upload File Modal
	ALPHA_Upload_Modal = {

		$container: 		$( '#alpha-upload-modal #alpha-drag-drop-area' ),
		$progressPercent: 	$( '#alpha-progress-percent' ),
		$progressText: 		$( '#alpha-progress-text' ),
		$progressError: 	$( '#alpha-progress-error' ),

		uploader: {},

		init: function( options ) {
			this.options = options;

			// Init pluploader
			this.uploader = new plupload.Uploader( this.options );
			this.uploader.init();

			this.uploadListeners();
		},

		uploadListeners: function() {
			var self = this;

			// File added to queue
			this.uploader.bind( 'FilesAdded', function( up, file ) {
				self.$container.addClass( 'uploading' );
				self.$progressError.hide();

				up.refresh();
				up.start();
			} );

			// Progress bar
			this.uploader.bind( 'UploadProgress', function( up, file ) {
				self.$progressPercent.css( 'width', file.percent + '%' );
				self.$progressText.html( file.percent + '%' );
			} );

			// File uploaded
			this.uploader.bind( 'FileUploaded', function( up, file, response ) {
				var response = $.parseJSON( response.response );

		 		if( response.error && response.error.code ) {
		 			self.uploader.trigger('Error', {
		            	code : response.error.code,
		            	message : response.error.message,
		            	file : file
		        	});
		 		}
		 		else {
			 		ALPHA_Admin_Download.addFile( response.file.url );
			 		if ( response.file.hasOwnProperty('thumbnail') && response.file.thumbnail !== false ) {
  			 		if ( response.file.thumbnail.hasOwnProperty('meta_box') && response.file.thumbnail.meta_box !== false ) {
              WPSetThumbnailHTML(response.file.thumbnail.meta_box);
  			 		}
			 		}
			 		// Close modal and hide progress bar
			 		setTimeout( function() {
			 			$( 'body' ).trigger( 'closeModal' );

			 			setTimeout( function() {
			 				self.$container.removeClass( 'uploading' );
			 			}, 300 );

			 		}, 1000 );
		 		}
			} );

			// Error
			this.uploader.bind( 'Error', function( up, err ) {
				self.$progressError. html( '<p>' + err.message + '</p>' ).show();
			 	self.$container.removeClass( 'uploading' );

				up.refresh();
			} );
		}

	}

	// Existing File Modal
	ALPHA_Existing_Modal = {

		$file_url: 	$( '#alpha-file-url' ),
		$confirm: 	$( '#alpha-select-done' ),

		options: {},

		init: function( options ) {
			this.options = options;
			this.fileBrowser();
			this.confirm();
			this.focus();
		},

		fileBrowser: function() {
			var self = this;

			// Init file browser
			$( '#alpha-file-browser' ).fileTree( this.options, function( file ) {
				// User clicked file, update URL field
				var file_path = file.replace( self.options.root, self.options.url );
				self.$file_url.val( file_path );
			} );
		},

		confirm: function() {
			var self = this;

			// Done with existing file modal
			self.$confirm.on( 'click', function( e ) {
				var url = self.$file_url.val();
				ALPHA_Admin_Download.addFile( url );

				$( 'body' ).trigger( 'closeModal' );

				e.preventDefault();
			} );
		},

		focus: function() {
			var self = this;

			$( 'body' ).on( 'click', '.alpha-modal-action.select-existing', function() {
				self.$file_url.focus();
			} );
		}

	};

	ALPHA_Admin_Download.init( updateStatusArgs );
	ALPHA_Upload_Modal.init( pluploadArgs );
	ALPHA_Existing_Modal.init( fileBrowserArgs );

} );
