<?php
namespace MediaWiki\Skins\Vector\Components;

/**
 * VectorComponentTableOfContents component
 */
class VectorComponentTableOfContents implements VectorComponent {

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$pinnableElementName = 'vector-toc';
		$pinnedContainer = new VectorComponentPinnedContainer( $pinnableElementName );
		$pinnableElement = new VectorComponentPinnableElement( $pinnableElementName );
		return $pinnableElement->getTemplateData() + $pinnedContainer->getTemplateData();
	}
}
