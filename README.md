Vector Skin
========================

Installation
------------

See <https://www.mediawiki.org/wiki/Skin:Vector>

Development
-----------

### Coding conventions

We strive for compliance with MediaWiki conventions:

<https://www.mediawiki.org/wiki/Manual:Coding_conventions>

Additions and deviations from those conventions that are more tailored to this
project are noted at:

<https://www.mediawiki.org/wiki/Reading/Web/Coding_conventions>

### Configuration options

#### $wgVectorPrintLogo

Logo used in print styles. Keys are `url`, `width`, and `height` (in
pixels). Note that this solution only works correctly if the image
pointed to by `url` is an SVG that does not specify width and height
attributes, or its width and height match the corresponding variables
below. Alternatively, a PNG or other type of image can be used, but
its dimensions also need to match the corresponding variable below.
That in turn may result in blurry images, though.

The URL can be absolute or relative.

Example configuration:

	$wgVectorPrintLogo = [
		'url' => 'https://en.wikipedia.org/static/images/mobile/copyright/wikipedia-wordmark-en.svg',
		'width' => 174,
		'height' => 27
	];

* Type: `Array`
* Default: `false`
