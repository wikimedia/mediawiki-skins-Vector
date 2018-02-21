# Selenium tests

Please see tests/selenium/README.md file in mediawiki/core repository (usually at mediawiki/vagrant/mediawiki).

## Usage

Set up MediaWiki-Vagrant:

    cd mediawiki/vagrant
    vagrant up
    vagrant provision
    cd mediawiki
    npm install

Run both mediawiki/core and skin tests from mediawiki/core folder:

    npm run selenium

To run only skin tests, first in one terminal tab (or window) start Chromedriver:

    chromedriver --url-base=wd/hub --port=4444

Then, in another terminal tab (or window) go to mediawiki/core folder:

    ./node_modules/.bin/wdio tests/selenium/wdio.conf.js --spec skins/SKIN-NAME/tests/selenium/specs/*.js

Run only one skin test file from mediawiki/core folder:

    ./node_modules/.bin/wdio tests/selenium/wdio.conf.js --spec skins/SKIN-NAME/tests/selenium/specs/FILE-NAME.js

To run only one skin test from mediawiki/core folder (name contains string 'TEST-NAME'):

    ./node_modules/.bin/wdio tests/selenium/wdio.conf.js --spec skins/SKIN-NAME/tests/selenium/specs/FILE-NAME.js --mochaOpts.grep preferences TEST-NAME
