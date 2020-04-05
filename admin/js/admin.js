( function( $ ) {
	'use strict';
	jQuery( document ).ready( function () {

		jQuery( '[data-list-of-templates]' ).each( function( index, container ) {

			var $container = jQuery( container ),
				$list = $container.find( '.list' ).empty(),
				template = wp.template( $container.find( '[id^=tmpl]' ).eq( 0 ).attr( 'id' ).slice( 5 ) ),
				data = eval(  $container.attr( 'data-list-of-templates' ) + '_data' ),
				count = data.length;

			function getRowNumber( element ) {
				return $list.find( '.list-item' ).index( jQuery( element ).closest( '.list-item' ) );
			}

			function getListLength() {
				return $list.find( '.list-item' ).length;
			}

			function add() {
				count = count + 1;
				var item = new Object();
				Object.assign( item, { value: '' }, { i: count } );
				$list.append( template( item ) );
			}

			function remove() {
				$list.find( '.list-item' ).eq( getRowNumber( this ) ).remove();
				if ( getListLength() == 0 ) {
					add();
				}
			}

			function build() {
				if ( count == 0 ) {
					add();
				} else {
					jQuery.each( data, function( index, value ) {
						var item = new Object();
						Object.assign( item, value, { i: getListLength() + 1 } );
						$list.append( template( item ) );
					} );
				}
			}

			build();
			$container.on( 'click', 'button.add-button', add );
			$container.on( 'click', 'button.remove-button', remove )

		} );
	} );
} )( jQuery );





( function( $ ) {
	'use strict';
	jQuery( document ).ready( function () {
		jQuery( 'body' ).on( 'click', '.file-choice-field .file-choice-button', function () {
			var $control = jQuery( this ).closest( '.file-choice-field' ).find( '.file-choice-control' ),
				file = wp.media( { 
				multiple: false,
				library: {},
			} ).open()
			.on( 'select', function( e ) {
				var uploaded_file = file.state().get( 'selection' ).first();
				var file_url = uploaded_file.toJSON().url;
				console.log( file_url );
				$control.val( file_url );
			} );
		} )
	} );
} )( jQuery );




( function( $ ) {
	'use strict';
	jQuery( document ).ready( function () {
		jQuery( '.data-picker-control' ).wpColorPicker();
		jQuery( 'body' ).on( 'click', '.data-picker-control', function () {
			jQuery( this ).wpColorPicker();
		} );
	} );
} )( jQuery );