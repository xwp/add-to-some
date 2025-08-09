/**
 * Native Share Handler for Add-to-Some Plugin
 * 
 * Handles native sharing functionality. If Web Share API is not available,
 * the native share links are hidden from the user interface.
 * 
 * @since 1.0.0
 */
( function() {
	'use strict';

	/**
	 * DOM ready utility function.
	 * 
	 * @param {Function} callback Function to execute when DOM is ready.
	 */
	function domReady( callback ) {
		if ( 'loading' !== document.readyState ) {
			callback();
		} else {
			document.addEventListener( 'DOMContentLoaded', callback );
		}
	}

	/**
	 * Check if the Web Share API is available.
	 * 
	 * @return {boolean} True if native sharing is supported.
	 */
	function isNativeShareSupported() {
		return navigator.share && 'function' === typeof navigator.share;
	}

	/**
	 * Handle click events on native share links.
	 * 
	 * @param {Event} event The click event object.
	 */
	function handleShareClick( event ) {
		event.preventDefault();

		const linkElement = event.currentTarget;
		const shareData = {
			title: linkElement.getAttribute( 'data-title' ) || document.title,
			text: linkElement.getAttribute( 'data-text' ) || '',
			url: linkElement.getAttribute( 'data-url' ) || linkElement.href,
		};

		// Use native sharing (we know it's available at this point)
		navigator.share( shareData ).catch( function( error ) {
			// Log error for debugging but don't show to user
			if ( window.console && window.console.error ) {
				window.console.error( 'Native share failed:', error );
			}
		} );
	}

	/**
	 * Initialize native share functionality.
	 * Shows or hides native share links based on browser support.
	 */
	function initializeNativeShare() {
		const shareLinks = document.querySelectorAll( '.ats-native-share' );

		if ( ! shareLinks.length ) {
			return;
		}

		if ( isNativeShareSupported() ) {
			// Native sharing is supported - show links and attach handlers
			Array.prototype.forEach.call( shareLinks, function( link ) {
				link.style.display = '';
				link.addEventListener( 'click', handleShareClick, false );
			} );
		} else {
			// Native sharing not supported - hide the links
			Array.prototype.forEach.call( shareLinks, function( link ) {
				link.style.display = 'none';
			} );
		}
	}

    /**
     * Initialize desktop popup behavior for share links.
     * Applies to non-native share anchors inside the Add-to-Some icons list.
     */
    function initializeSharePopup() {
        // Only on desktop-sized viewports.
        if ( window.innerWidth < 1024 ) {
            return;
        }

        const shareAnchors = document.querySelectorAll( '.add-to-some__icon a' );

        if ( ! shareAnchors.length ) {
            return;
        }

        Array.prototype.forEach.call( shareAnchors, function( anchor ) {
            // Skip native share triggers handled elsewhere.
            if ( 
              anchor.classList && 
              ( anchor.classList.contains( 'ats-native-share' ) || anchor.classList.contains( 'ats-email-share' ) )
            ) {
                return;
            }

            anchor.addEventListener( 'click', function( event ) {
                // Only handle normal left-click without modifier keys.
                if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
                    return;
                }

                const href = anchor.getAttribute( 'href' );
                if ( ! href ) {
                    return;
                }

                event.preventDefault();

                const popupWidth = 600;
                const popupHeight = 600;

                // Cross-browser offsets for dual-screen setups.
                const dualScreenLeft = ( window.screenLeft !== undefined ) ? window.screenLeft : ( window.screenX || 0 );
                const dualScreenTop = ( window.screenTop !== undefined ) ? window.screenTop : ( window.screenY || 0 );

                // Viewport size fallbacks.
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth || screen.width;
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight || screen.height;

                const left = dualScreenLeft + Math.max( 0, ( viewportWidth - popupWidth ) / 2 );
                const top = dualScreenTop + Math.max( 0, ( viewportHeight - popupHeight ) / 2 );

                const features = [
                    'menubar=no',
                    'toolbar=no',
                    'resizable=yes',
                    'scrollbars=yes',
                    'noopener',
                    'noreferrer',
                    'height=' + popupHeight,
                    'width=' + popupWidth,
                    'left=' + Math.round( left ),
                    'top=' + Math.round( top ),
                ].join( ',' );

                const popup = window.open( href, '', features );
                if ( popup && popup.focus ) {
                    popup.focus();
                }
            }, false );
        } );
    }

	// Initialize when DOM is ready
    domReady( initializeNativeShare );
    domReady( initializeSharePopup );

}() );
