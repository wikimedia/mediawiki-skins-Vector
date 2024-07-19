const makeTemplate = function () {
	const templateString = `<div class="cdx-dialog-backdrop">
    <div tabindex="0"></div>
    <div class="cdx-dialog cdx-dialog--horizontal-actions" role="dialog" aria-labelledby="cdx-dialog-label-0" aria-modal="true">
        <header class="cdx-dialog__header cdx-dialog__header--default">
            <div class="cdx-dialog__header__title-group">
                <h2 id="cdx-dialog-label-0" class="cdx-dialog__header__title">
                    ${ mw.message( 'vector-night-mode-launch-title' ).escaped() }
                </h2>
            </div>
            <button class="cdx-button cdx-button--action-default cdx-button--weight-quiet cdx-button--size-medium cdx-button--icon-only cdx-dialog__header__close-button" type="button" aria-label="${ mw.message( 'vector-night-mode-launch-close-label' ).escaped() }">
                <span class="cdx-icon cdx-icon--medium">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" viewBox="0 0 20 20"><title>Close</title><g><path d="m4.34 2.93 12.73 12.73-1.41 1.41L2.93 4.35z"></path><path d="M17.07 4.34 4.34 17.07l-1.41-1.41L15.66 2.93z"></path></g></svg>
                </span>
            </button>
        </header>
        <div class="cdx-dialog-focus-trap" tabindex="-1"></div>
        <div class="cdx-dialog__body">
            <div class="skin-vector-launch-image"></div>
            <h3>
            ${ mw.message( 'vector-night-mode-launch-subtitle' ).escaped() }
            </h3>
            <p>
                ${ mw.message( 'vector-night-mode-launch-description-day' ).escaped() }
            </p>
        </div>
        <footer class="cdx-dialog__footer cdx-dialog__footer--default"></footer>
    </div>
    <div tabindex="0"></div>
</div>`;

	const templateElement = document.createElement( 'div' );
	templateElement.id = 'vector-dark-mode-launch-banner';
	templateElement.innerHTML = templateString;
	return templateElement;
};

function closeModal() {
	const modal = document.getElementById( 'vector-dark-mode-launch-banner' );
	if ( modal ) {
		modal.remove();
	}
}

function init() {
	const mountElement = document.getElementById( 'mw-teleport-target' ),
		isNotSkinVector2022 = mw.config.get( 'skin' ) !== 'vector-2022',
		templateElement = makeTemplate(),
		dialogOverlay = templateElement.querySelector( '.cdx-dialog-backdrop' ),
		dialogCloseButton = templateElement.querySelector( '.cdx-dialog__header__close-button' );

	// since this can module can run via centralNotice, ensuring the currect skin is Vector 2022
	if ( isNotSkinVector2022 ) {
		return;
	}

	if ( dialogOverlay ) {
		dialogOverlay.addEventListener( 'click', ( e ) => {
			if ( e.target === dialogOverlay ) {
				closeModal();
			}
		} );
	}

	if ( dialogCloseButton ) {
		dialogCloseButton.addEventListener( 'click', closeModal );
	}

	if ( mountElement ) {
		mountElement.appendChild( templateElement );
	}
}

module.exports = init;
