<?php
namespace MediaWiki\Skins\Vector\Components;

/**
 * VectorComponentLanguageButton component
 */
class VectorComponentLanguageDropdown implements VectorComponent {
	private const CLASS_PROGRESSIVE = 'mw-ui-progressive';
	/** @var string */
	private $label;
	/** @var string */
	private $ariaLabel;
	/** @var string */
	private $class;
	/** @var int */
	private $numLanguages;
	/** @var array */
	private $menuContentsData;

	/**
	 * @param string $label human readable
	 * @param string $ariaLabel label for accessibility
	 * @param string $class of the dropdown component
	 * @param int $numLanguages
	 * @param string $itemHTML the HTML of the list e.g. `<li>...</li>`
	 * @param string $beforePortlet no known usages. Perhaps can be removed in future
	 * @param string $afterPortlet used by Extension:ULS
	 */
	public function __construct(
		string $label, string $ariaLabel, string $class, int $numLanguages,
		// @todo: replace with >MenuContents class.
		string $itemHTML, string $beforePortlet = '', string $afterPortlet = ''
	) {
		$this->label = $label;
		$this->ariaLabel = $ariaLabel;
		$this->class = $class;
		$this->numLanguages = $numLanguages;
		$this->menuContentsData = [
			'html-items' => $itemHTML,
			'html-before-portal' => $beforePortlet,
			'html-after-portal' => $afterPortlet,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$dropdown = new VectorComponentDropdown( 'p-lang-btn', $this->label, $this->class );
		$dropdownData = $dropdown->getTemplateData();
		// override default heading class.
		$dropdownData['heading-class'] = 'mw-ui-button mw-ui-quiet '
			. self::CLASS_PROGRESSIVE . ' mw-portlet-lang-heading-' . strval( $this->numLanguages );
		// ext.uls.interface attaches click handler to this selector.
		$dropdownData['checkbox-class'] = ' mw-interlanguage-selector';
		// Override header icon (currently no way to do this using constructor)
		$dropdownData['html-vector-heading-icon'] = '<span class="mw-ui-icon ' .
			'mw-ui-icon-wikimedia-language-progressive"></span>';
		$dropdownData['aria-label'] = $this->ariaLabel;
		return $dropdownData + $this->menuContentsData;
	}
}
