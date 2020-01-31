#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

mkdir -p .storybook/resolve-less-imports/mediawiki.ui

curl "https://en.wikipedia.org/w/load.php?only=styles&skin=vector&debug=true&modules=ext.echo.styles.badge|ext.uls.pt|wikibase.client.init|mediawiki.skinning.interface" -o .storybook/integration.less
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.less?view=raw" -o .storybook/resolve-less-imports/mediawiki.mixins.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.ui/variables.less?view=raw" -o .storybook/resolve-less-imports/mediawiki.ui/variables.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.rotation.less?view=raw" -o .storybook/resolve-less-imports/mediawiki.mixins.rotation.less -L
curl "https://phabricator.wikimedia.org/source/mediawiki/browse/master/resources/src/mediawiki.less/mediawiki.mixins.animation.less?view=raw" -o .storybook/resolve-less-imports/mediawiki.mixins.animation.less -L
