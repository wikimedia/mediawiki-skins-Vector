#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

mkdir -p .storybook/resolve-less-imports/mediawiki.ui

curl "https://en.wikipedia.org/w/load.php?only=styles&skin=vector&debug=true&modules=ext.echo.styles.badge|ext.uls.pt|wikibase.client.init|mediawiki.skinning.interface" -o .storybook/integration.less
curl -L "https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/master/resources/src/mediawiki.less/mediawiki.mixins.less?format=TEXT" | base64 --decode > .storybook/resolve-less-imports/mediawiki.mixins.less
curl -L "https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/master/resources/src/mediawiki.less/mediawiki.ui/variables.less?format=TEXT" | base64 --decode > .storybook/resolve-less-imports/mediawiki.ui/variables.less
curl -L "https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/master/resources/src/mediawiki.less/mediawiki.mixins.rotation.less?format=TEXT" | base64 --decode > .storybook/resolve-less-imports/mediawiki.mixins.rotation.less
curl -L "https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/master/resources/src/mediawiki.less/mediawiki.mixins.animation.less?format=TEXT" | base64 --decode > .storybook/resolve-less-imports/mediawiki.mixins.animation.less