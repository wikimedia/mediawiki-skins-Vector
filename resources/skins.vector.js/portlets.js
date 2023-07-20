/**
 * An object containing the data to help create a portlet.
 *
 * @typedef {Object} Hint
 * @property {string} type
 */

/**
 * Creates default portlet.
 *
 * @param {HTMLElement} portlet
 * @return {HTMLElement}
 */
function addDefaultPortlet( portlet ) {
	const ul = portlet.querySelector( 'ul' );
	if ( !ul ) {
		return portlet;
	}
	ul.classList.add( 'vector-menu-content-list' );
	const label = portlet.querySelector( 'label' );
	if ( label ) {
		const labelDiv = document.createElement( 'div' );
		labelDiv.classList.add( 'vector-menu-heading' );
		labelDiv.innerHTML = label.innerText;
		label.remove();
		portlet.insertBefore( labelDiv, ul );
	}
	let wrapper = portlet.querySelector( 'div:last-child' );
	if ( wrapper ) {
		ul.remove();
		wrapper.appendChild( ul );
		wrapper.classList.add( 'vector-menu-content' );
	} else {
		wrapper = document.createElement( 'div' );
		wrapper.classList.add( 'vector-menu-content' );
		ul.remove();
		wrapper.appendChild( ul );
		portlet.appendChild( wrapper );
	}
	portlet.classList.add( 'vector-menu' );
	return portlet;
}

/**
 * A hook handler for util.addPortlet hook.
 * It creates a portlet based on the hint, and adabt it to vector skin.
 *
 * @param {HTMLElement} portlet
 * @return {HTMLElement}
 */
function addPortletHandler( portlet ) {
	portlet.classList.remove( 'mw-portlet-js' );
	return addDefaultPortlet( portlet );
}

mw.hook( 'util.addPortlet' ).add( addPortletHandler );

/**
 *
 * @return {{addPortletHandler: (function(HTMLElement): HTMLElement)}}
 */
function main() {
	return {
		addPortletHandler
	};
}

module.exports = {
	main, addPortletHandler
};
