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
		const shareLinks = document.querySelectorAll( '.xwp-ats-native-share' );

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

	// Initialize when DOM is ready
	domReady( initializeNativeShare );

}() );
