#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

mkdir -p .storybook/resolve-less-imports/mediawiki.ui
mkdir -p docs/ui/assets/

curl "https://en.wikipedia.org/w/load.php?only=styles&skin=vector&debug=true&modules=ext.echo.styles.badge|ext.uls.pt|wikibase.client.init|mediawiki.skinning.interface" -o .storybook/integration.less
curl -L "https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/master/resources/src/mediawiki.less/mediawiki.mixins.less?format=TEXT" | base64 --decode > .storybook/resolve-less-imports/mediawiki.mixins.less
curl -L "https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/master/resources/src/mediawiki.less/mediawiki.ui/variables.less?format=TEXT" | base64 --decode > .storybook/resolve-less-imports/mediawiki.ui/variables.less
curl -L "https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/master/resources/src/mediawiki.less/mediawiki.mixins.rotation.less?format=TEXT" | base64 --decode > .storybook/resolve-less-imports/mediawiki.mixins.rotation.less
curl -L "https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/core/+/master/resources/src/mediawiki.less/mediawiki.mixins.animation.less?format=TEXT" | base64 --decode > .storybook/resolve-less-imports/mediawiki.mixins.animation.less
curl "https://en.m.wikipedia.org/static/images/mobile/copyright/wikipedia-wordmark-en.svg" -o "docs/ui/assets/wordmark.svg"
curl "https://en.m.wikipedia.org/static/images/mobile/copyright/wikipedia.png" -o "docs/ui/assets/icon.png"
# FIXME: Update to en.wikipedia.org uris when available.
curl "https://di-logo-sandbox.firebaseapp.com/img/tagline/en-tagline-117-13.svg" -o "docs/ui/assets/tagline.svg"
