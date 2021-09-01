module.exports = function () {
	var header = document.getElementById( 'vector-sticky-header' );
	if ( !header ) {
		return;
	}
	// TODO: Use IntersectionObserver
	header.classList.add( 'vector-sticky-header-visible' );
};
