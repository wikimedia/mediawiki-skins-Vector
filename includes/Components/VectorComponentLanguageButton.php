<?php
namespace MediaWiki\Skins\Vector\Components;

use MediaWiki\Skins\Vector\Hooks;

/**
 * VectorComponentLanguageButton component
 */
class VectorComponentLanguageButton implements VectorComponent {

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		return [
			'id' => 'p-lang-btn-sticky-header',
			'class' => 'mw-interlanguage-selector',
			'is-quiet' => true,
			'tabindex' => '-1',
			'html-vector-button-icon' => Hooks::makeIcon( 'wikimedia-language' ),
			'event' => 'ui.dropdown-p-lang-btn-sticky-header'
		];
	}
}
