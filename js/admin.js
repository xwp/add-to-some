(function(){
  'use strict';

  /**
   * Run a callback after DOM is ready.
   *
   * @param {Function} callback Function to run once DOM is interactive/complete.
   * @returns {void}
   */
  function ready( callback ) {
    if ( document.readyState !== 'loading' ) {
      callback();
    } else {
      document.addEventListener( 'DOMContentLoaded', callback );
    }
  }

  /**
   * Toggle visibility of sub-options within a row according to its checkbox state.
   *
   * @param {HTMLElement} row Row element containing a checkbox and optional sub-options.
   * @returns {void}
   */
  function toggleSubOptions( row ) {
    var checkbox = row.querySelector( 'input[type="checkbox"]' );
    var subs = row.querySelectorAll( '.xwp-ats-suboptions' );
    if ( ! checkbox || ! subs.length ) {
      return;
    }

    /**
     * Synchronize sub-option visibility with checkbox state.
     *
     * @returns {void}
     */
    function sync() {
      for ( var i = 0; i < subs.length; i++ ) {
        subs[ i ].style.display = checkbox.checked ? '' : 'none';
      }
    }

    checkbox.addEventListener( 'change', sync );
    sync();
  }

  /**
   * Enable drag-and-drop reordering for the Share Buttons list and
   * persist the order into a hidden input field.
   *
   * @returns {void}
   */
  function makeReorderable() {
    var container = document.getElementById( 'xwp-ats-buttons' );
    var hidden = document.getElementById( 'xwp-ats-order' );
    if ( ! container || ! hidden ) {
      return;
    }

    var dragSrc = null;

    /**
     * Serialize the current order of rows into the hidden input as a
     * comma-separated list of keys.
     *
     * @returns {void}
     */
    function serialize() {
      var rows = container.querySelectorAll( '.xwp-ats-row' );
      var keys = [];
      for ( var i = 0; i < rows.length; i++ ) {
        var key = rows[ i ].getAttribute( 'data-key' );
        if ( key ) {
          keys.push( key );
        }
      }
      hidden.value = keys.join( ',' );
    }

    /**
     * Handle drag start: mark source and set transferable data for compatibility.
     *
     * @param {DragEvent} event Drag start event.
     * @returns {void}
     */
    function handleDragStart( event ) {
      var row = event.currentTarget;
      dragSrc = row;
      if ( event.dataTransfer ) {
        event.dataTransfer.effectAllowed = 'move';
        // Some browsers require data to be set for drag-and-drop to initiate.
        try {
          event.dataTransfer.setData( 'text/plain', row.getAttribute( 'data-key' ) || '' );
        } catch ( _err ) { /* noop */ }
      }
      row.classList.add( 'xwp-ats-dragging' );
    }

    /**
     * Handle drag over: reorder rows visually based on pointer position.
     *
     * @param {DragEvent} event Drag over event.
     * @returns {void}
     */
    function handleDragOver( event ) {
      if ( ! dragSrc ) {
        return;
      }

      event.preventDefault(); // Allow drop.
      if ( event.dataTransfer ) {
        event.dataTransfer.dropEffect = 'move';
      }

      var target = event.target.closest( '.xwp-ats-row' );
      if ( ! target || target === dragSrc || target.parentNode !== container ) {
        return;
      }

      var rect = target.getBoundingClientRect();
      var before = ( event.clientY - rect.top ) < ( rect.height / 2 );
      container.insertBefore( dragSrc, before ? target : target.nextSibling );
    }

    /**
     * Handle drop: prevent default to avoid browser navigation.
     *
     * @param {DragEvent} event Drop event.
     * @returns {void}
     */
    function handleDrop( event ) {
      event.preventDefault();
    }

    /**
     * Handle drag end: clear state and update serialized order.
     *
     * @returns {void}
     */
    function handleDragEnd() {
      if ( dragSrc ) {
        dragSrc.classList.remove( 'xwp-ats-dragging' );
      }
      dragSrc = null;
      serialize();
    }

    container.addEventListener( 'dragover', handleDragOver );
    container.addEventListener( 'drop', handleDrop );

    var rows = container.querySelectorAll( '.xwp-ats-row' );
    for ( var i = 0; i < rows.length; i++ ) {
      rows[ i ].addEventListener( 'dragstart', handleDragStart );
      rows[ i ].addEventListener( 'dragend', handleDragEnd );
    }
  }

  ready( function initAdminShareButtons() {
    var rows = document.querySelectorAll( '.xwp-ats-row' );
    for ( var i = 0; i < rows.length; i++ ) {
      toggleSubOptions( rows[ i ] );
    }
    makeReorderable();
  } );
})();
