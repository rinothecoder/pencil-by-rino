/**
 * Pencil by Rino - admin page tabs and copy buttons.
 */
( function () {
	'use strict';

	var links  = document.querySelectorAll( '[data-tab]' );
	var panels = document.querySelectorAll( '[data-tab-panel]' );

	if ( ! links.length || ! panels.length ) {
		return;
	}

	/**
	 * Show one panel and mark its link as active.
	 *
	 * @param {string} name Tab name.
	 */
	function showTab( name ) {
		var found = false;

		panels.forEach( function ( panel ) {
			var match = panel.getAttribute( 'data-tab-panel' ) === name;
			panel.hidden = ! match;
			found = found || match;
		} );

		if ( ! found ) {
			showTab( panels[ 0 ].getAttribute( 'data-tab-panel' ) );
			return;
		}

		links.forEach( function ( link ) {
			var active = link.getAttribute( 'data-tab' ) === name;
			link.classList.toggle( 'pencil-header__link--active', active );
			if ( active ) {
				link.setAttribute( 'aria-current', 'page' );
			} else {
				link.removeAttribute( 'aria-current' );
			}
		} );
	}

	links.forEach( function ( link ) {
		link.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			var name = link.getAttribute( 'data-tab' );
			showTab( name );
			// Keep the tab in the URL so a reload lands on the same one.
			window.history.replaceState( null, '', '#' + name );
		} );
	} );

	showTab( window.location.hash.replace( '#', '' ) );

	// A link to admin.php?page=pencil#get-started from elsewhere in the admin.
	window.addEventListener( 'hashchange', function () {
		showTab( window.location.hash.replace( '#', '' ) );
	} );

	// Sub-tabs inside a panel (Get started: For you / For AI agents).
	document.querySelectorAll( '[data-subtabs]' ).forEach( function ( group ) {
		var buttons = group.querySelectorAll( '[data-subtab]' );
		var subpanels = group.querySelectorAll( '[data-subtab-panel]' );

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var name = button.getAttribute( 'data-subtab' );

				buttons.forEach( function ( other ) {
					var active = other === button;
					other.classList.toggle( 'pencil-subtabs__btn--active', active );
					other.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				} );

				subpanels.forEach( function ( panel ) {
					panel.hidden = panel.getAttribute( 'data-subtab-panel' ) !== name;
				} );
			} );
		} );
	} );

	// Copy buttons read the text of the element they point to.
	document.querySelectorAll( '[data-copy]' ).forEach( function ( button ) {
		var source  = document.getElementById( button.getAttribute( 'data-copy' ) );
		var idle    = button.textContent;
		var done    = button.getAttribute( 'data-copied-label' ) || idle;

		if ( ! source ) {
			return;
		}

		function confirmCopied() {
			button.textContent = done;
			window.setTimeout( function () {
				button.textContent = idle;
			}, 1800 );
		}

		// Older browsers, or a page without clipboard permission: select the
		// text and use the legacy copy command, which still works on a click.
		function legacyCopy() {
			source.focus();
			if ( source.select ) {
				source.select();
			}
			try {
				if ( document.execCommand( 'copy' ) ) {
					confirmCopied();
				}
			} catch ( e ) {
				// The text stays selected so the user can copy it by hand.
			}
		}

		button.addEventListener( 'click', function () {
			var text = source.value !== undefined ? source.value : source.textContent;

			if ( ! navigator.clipboard || ! navigator.clipboard.writeText ) {
				legacyCopy();
				return;
			}

			navigator.clipboard.writeText( text ).then( confirmCopied ).catch( legacyCopy );
		} );
	} );
}() );
