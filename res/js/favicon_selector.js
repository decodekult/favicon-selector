var $fs_favicon_data;

jQuery( document ).ready( function() {
	$fs_favicon_data = jQuery( '.js-fs-options input' ).serialize();
});

jQuery( document ).on( 'change', '.js-fs-favicon-item, .js-fs-favicon-dashboard', function() {
	if ( jQuery( '.js-fs-options input' ).serialize() != $fs_favicon_data ) {
		jQuery( '.js-fs-save-settings' ).addClass( 'button-primary' ).attr( 'disabled', false );
	} else {
		jQuery( '.js-fs-save-settings' ).removeClass( 'button-primary' ).attr( 'disabled', true );
	}
});

jQuery( document ).on( 'click', '.js-fs-save-settings', function() {
	var data = {
		action: 'fs_save_settings',
		favicon_data: jQuery( '.js-fs-options input' ).serialize(),
		wp_nonce: jQuery( '#fs_save_settings_nonce' ).val()
	},
	spinnerContainer = jQuery( '<div class="spinner ajax-loader">' ).insertAfter( jQuery(this) ).show();
	jQuery.ajax({
		async:false,
		type:"POST",
		url:ajaxurl,
		data:data,
		success: function( response ) {
			if ( ( typeof( response ) !== 'undefined') && response == 'ok' ) {
				$fs_favicon_data = jQuery( '.js-fs-options input' ).serialize();
				jQuery( '.js-fs-save-settings' ).removeClass( 'button-primary' ).attr( 'disabled', true );
			} else {
				//console.log( "Error: AJAX returned ", response );
			}
		},
		error: function( ajaxContext ) {
			//console.log( "Error: ", ajaxContext.responseText );
		},
		complete: function() {
			spinnerContainer.remove();
		}
	});
});