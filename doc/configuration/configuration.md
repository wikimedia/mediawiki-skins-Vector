# Vector Configuration Options and User Preferences

For documentation on Minerva preferences and configuration options see [https://github.com/wikimedia/mediawiki-skins-MinervaNeue/blob/master/README.md](https://github.com/wikimedia/mediawiki-skins-MinervaNeue/blob/master/README.md)

## Topics

[Using Query Parameters](#using-query-parameters)

[Configuration Options Glossary](#configuration-options-glossary)

[Developer Only Configuration](#developer-only-configuration-temporary)

[Site-level Configuration](#site-level-configuration)

[User Preference Options Glossary](#user-preference-options-glossary)

## Using Query Parameters

Query parameters can be used to override user preferences and/or configuration within Vector.  We typically use these during development to aid testing and perform community outreach. They are not intended to be used by standard users and should always be considered temporary.

The following are examples of query parameter usage:

In the URL:

- https://en.wikipedia.beta.wmflabs.org/wiki/Spain?vectormainmenupinned=0

- https://en.wikipedia.beta.wmflabs.org/wiki/Spain?vectormainmenupinned=1

- https://en.wikipedia.beta.wmflabs.org/wiki/Spain?vectorpagetoolspinned=0

- https://en.wikipedia.beta.wmflabs.org/wiki/Spain?vectorpagetoolspinned=1

Note: There is currently a bug relating to certain querystring parameters which is documented in [https://phabricator.wikimedia.org/T347900](https://phabricator.wikimedia.org/T347900)


## Configuration Options Glossary

Each option controls specific aspects of the Vector skin's behavior and appearance, and some are configurable per-wiki to accommodate diverse preferences and requirements.

The following explains each configuration option in the `InitialiseSettings.php` file from the `mediawiki-config` repo:


## Developer-only Configuration (Temporary)

Certain configuration is used by us during rollout of new features with the expectation that they will later be removed. At times temporary configuration may become Site-level configuration if new requirements emerge post-deployment.

Do not rely on any of the feature flags documented here. They should not be considered stable.

- wgVectorPromoteAddTopic

  - Determines whether the Add topic feature is promoted in discussions.

  - Default: `false`

  - Removal ticket: [https://phabricator.wikimedia.org/T331312](https://phabricator.wikimedia.org/T331312)

- wgVectorDefaultSkinVersionForExistingAccounts

  - Sets the default skin version for existing accounts in Vector skin. Exists to assist roll out of desktop improvements project.

  - Default: `'2'`

  - `legacy-vector` set to `'1'`.

  - Removal ticket:  [https://phabricator.wikimedia.org/T358273](https://phabricator.wikimedia.org/T358273)

- wgVectorDefaultSkinVersionForNewAccounts

  - Sets the default skin version for new accounts in Vector skin. Exists to assist roll out of desktop improvements project.

  - Default: `'2'`

  - `legacy-vector` set to `'1'`.

  - Removal ticket: [https://phabricator.wikimedia.org/T358273](https://phabricator.wikimedia.org/T358273)

- wgVectorLanguageInMainPageHeader

  - Shows language selector beside the main page title.

  - Removal ticket: [https://phabricator.wikimedia.org/T179159](https://phabricator.wikimedia.org/T179159)

- wgVectorNightMode

  - Enables the night mode feature in client preferences

  - Removal ticket: [https://phabricator.wikimedia.org/T179159](https://phabricator.wikimedia.org/T179159)

- wgVectorResponsive

  - Enables an experimental responsive version of the Vector 2022 skin.

  - Removal ticket: [https://phabricator.wikimedia.org/T106463](https://phabricator.wikimedia.org/T106463)

- wgVectorWrapTablesTemporary
   - When enabled, certain tables will be wrapped in a div to make them horizontally scrollable when no
     space is available.
  - Removal ticket: [https://phabricator.wikimedia.org/T361737](https://phabricator.wikimedia.org/T361737)

## Site-level Configuration

Different projects have different needs, so some configuration on the site level is necessary. Site level configuration should be considered permanent and removing configuration should be carefully managed and based on usage.

- wgVectorTableOfContentsCollapseAtCount

  - Sets the number of sections at which the Table of Contents collapses.

  - Default: `28`

- wgVectorMaxWidthOptions

  - Configures maximum width options for the Vector skin.

  - Defines exclusions and inclusions for specific pages.

- wgVectorLanguageInHeader

  - Controls language display in the header for the Vector skin. When disabled languaged appear in the sidebar.

  - Configurable for different wikis. This is currently used on projects like Wikimedia Commons, MediaWiki and Wikidata which do not have separate language sites.

- wgVectorWvuiSearchOptions

  - Configures search options for the Vector skin.

  - Includes thumbnail and description display.

- wgVectorSearchApiUrl

  - Allows site to specify an alternative API for search queries. If not set uses default MediaWiki search. Mostly used for development purposes.

- wgVectorSearchRecommendationsApiUrl

  - Allows site to specify an alternative API for search queries. Disabled by default.

  - Can be set to external service.

  - Default: `null`

Example:
  ```
  $wgVectorSearchRecommendationsApiUrl = 'https://en.wikipedia.org/w/rest.php/v1/search/page?q=morelike:$1&limit=20';
  ```

## User Preference Options Glossary

The following are interface elements or settings that users can customize within the Vector skin, such as toggling night mode, adjusting the width of the content area, or changing the font size.

- Vector Limited Width (`vector-limited-width`): This preference allows the user to specify whether they prefer a limited width It can be set to either 1 (enabled) or 0 (disabled), which will stretch the article body to full width.

- Vector Page Tools Pinned (`vector-page-tools-pinned`): Indicates whether the user wants the page tools menu to be pinned (visible) to the right of the content body. It can be set to either 1 (pinned) or 0 (not pinned).

- Vector Main Menu Pinned (`vector-main-menu-pinned`): Specifies whether the user prefers to have the main menu pinned (visible) to the left of the content body. It can be set to either 1 (pinned) or 0 (not pinned).

- Vector Table of Contents (TOC) Pinned (`vector-toc-pinned`): Determines if the user wants the table of contents on the left side to be pinned (visible) on desktop. It can be set to either 1 (pinned) or 0 (not pinned).

- Vector Appearance Pinned (`vector-appearance-pinned`): Indicates whether the user wants the appearance menu to be pinned (visible) on desktop. It can be set to either 1 (pinned) or 0 (not pinned).

- Vector Font Size (`vector-font-size`): Allows the user to select the preferred font size for the Vector. It can be set to 0 for regular, 1 for large, or 2 for x-large.

- Vector Night Mode (`vector-theme`): Specifies the preference for night mode in Vector. It can be set to 'day' for (disabled), 'night' (enabled), and 'os' (automatic based on system preferences). For logged in users this feature can be forced on via the ?vectornightmode=night or ?vectornightmode=1 query string parameter.
