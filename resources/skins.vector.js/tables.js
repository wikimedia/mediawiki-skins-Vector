const config = require( './config.json' );
const init = () => {
	if ( !config.VectorWrapTablesTemporary ) {
		return;
	}
	const tables = document.querySelectorAll( '.mw-parser-output table.wikitable' );
	Array.from( tables ).forEach( ( table ) => {
		const styles = window.getComputedStyle( table );
		const isFloat = styles.getPropertyValue( 'float' ) === 'right' || styles.getPropertyValue( 'float' ) === 'left';

		// Don't wrap tables within tables
		const parent = table.parentElement;
		if (
			parent &&
			!parent.matches( '.noresize' ) &&
			!parent.closest( 'table' ) &&
			!isFloat
		) {
			const wrapper = document.createElement( 'div' );
			wrapper.classList.add( 'noresize' );
			parent.insertBefore( wrapper, table );
			wrapper.appendChild( table );
		}
	} );
};

module.exports = {
	init
};
