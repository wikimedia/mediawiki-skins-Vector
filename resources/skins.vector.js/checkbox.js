/**
 * JavaScript enhancement for appropriately marked checkbox hacks
 *
 * The checkbox hack in Vector provides basic show/hide functionality with CSS
 * but JavaScript is used for progressive enhancements.
 *
 * This code targets any element with a mw-checkbox-hack-button class. It must have
 * a for attribute to qualify for enhancements.
 *
 * Enhancements include:
 * - Update `aria-role`s based on expanded/collapsed state.
 * - Update button icon based on expanded/collapsed state.
 *
 */

/** @interface MwApiConstructor */
/** @interface CheckboxHack */

var checkboxHack = /** @type {CheckboxHack} */ require( /** @type {string} */( 'mediawiki.page.ready' ) ).checkboxHack;

/**
 * Revise the button's `aria-expanded` state to match the checked state.
 *
 * @param {HTMLInputElement} checkbox
 * @param {HTMLElement} button
 * @return {void}
 * @ignore
 */
function updateAriaExpanded( checkbox, button ) {
	button.setAttribute( 'aria-expanded', checkbox.checked.toString() );
}

/**
 * Update the `aria-expanded` attribute based on checkbox state (target visibility) changes.
 *
 * @param {HTMLInputElement} checkbox
 * @param {HTMLElement} button
 * @return {function(): void} Cleanup function that removes the added event listeners.
 * @ignore
 */
function bindUpdateAriaExpandedOnInput( checkbox, button ) {
	var listener = updateAriaExpanded.bind( undefined, checkbox, button );
	// Whenever the checkbox state changes, update the `aria-expanded` state.
	checkbox.addEventListener( 'input', listener );

	return function () {
		checkbox.removeEventListener( 'input', listener );
	};
}

/**
 * Manually change the checkbox state when the button is focused and SPACE is pressed.
 *
 * @param {HTMLElement} button
 * @return {function(): void} Cleanup function that removes the added event listeners.
 * @ignore
 */
function bindToggleOnSpaceEnter( button ) {
	function isEnterOrSpace( /** @type {KeyboardEvent} */ event ) {
		return event.key === ' ' || event.key === 'Enter';
	}

	function onKeydown( /** @type {KeyboardEvent} */ event ) {
		// Only handle SPACE and ENTER.
		if ( !isEnterOrSpace( event ) ) {
			return;
		}
		// Prevent the browser from scrolling when pressing space. The browser will
		// try to do this unless the "button" element is a button or a checkbox.
		// Depending on the actual "button" element, this also possibly prevents a
		// native click event from being triggered so we programatically trigger a
		// click event in the keyup handler.
		event.preventDefault();
	}

	function onKeyup( /** @type {KeyboardEvent} */ event ) {
		// Only handle SPACE and ENTER.
		if ( !isEnterOrSpace( event ) ) {
			return;
		}

		// A native button element triggers a click event when the space or enter
		// keys are pressed. Since the passed in "button" may or may not be a
		// button, programmatically trigger a click event to make it act like a
		// button.
		button.click();
	}

	button.addEventListener( 'keydown', onKeydown );
	button.addEventListener( 'keyup', onKeyup );

	return function () {
		button.removeEventListener( 'keydown', onKeydown );
		button.removeEventListener( 'keyup', onKeyup );
	};
}

/**
 * Improve the interactivity of the sidebar panel by binding optional checkbox hack enhancements
 * for focus and `aria-expanded`. Also, flip the icon image on click.
 *
 * @param {HTMLElement|null} checkbox
 * @param {HTMLElement|null} button
 * @return {void}
 */
function initCheckboxHack( checkbox, button ) {
	if ( checkbox instanceof HTMLInputElement && button ) {
		checkboxHack.bindToggleOnClick( checkbox, button );
		bindUpdateAriaExpandedOnInput( checkbox, button );
		updateAriaExpanded( checkbox, button );
		bindToggleOnSpaceEnter( button );
	}
}

/**
 * Initialize all JavaScript sidebar enhancements.
 *
 * @param {Window} window
 */
function init( window ) {
	var buttons = window.document.querySelectorAll( '.mw-checkbox-hack-button' );

	Array.prototype.forEach.call( buttons, function ( button ) {
		var checkboxId = button.getAttribute( 'for' ),
			checkbox = checkboxId ? window.document.getElementById( checkboxId ) : null;

		if ( checkbox ) {
			initCheckboxHack( checkbox, button );
		}
	} );

}

module.exports = {
	init: init
};
