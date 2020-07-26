Vector Skin
========================

Installation
------------

See <https://www.mediawiki.org/wiki/Skin:Vector>.

### Configuration options

See [skin.json](skin.json).

Development
-----------

### NPM scripts

-   `install` / `i`: install project dependencies.
-   `run build`: compile source inputs to bundle outputs under `dist/`.
-   `start`: run Storybook development workflow.
-   `test` / `t`: build the project and execute all tests. Anything that can be validated
    automatically before publishing runs through this command.
-   `run doc`: generate all documentation under `docs/`.

Scripts containing `:` delimiters in their names are sub-scripts. They are invoked by the outermost
delimited name (and possibly other scripts). For example, `test:size` is executed by `test`.

Undocumented scripts are considered internal utilities and not expressly supported workflows.

💡 Tips:

-   Add `--` to pass arguments to the script command. For example, `npm run test:unit -- -u` to
    update snapshots or `npm run build -- -dw` to automatically rebuild a development output.
-   Add `-s` to omit verbose command echoing. For example, `npm -s i` or `npm -s run build`.

### Coding conventions

We strive for compliance with MediaWiki conventions:

<https://www.mediawiki.org/wiki/Manual:Coding_conventions>

Additions and deviations from those conventions that are more tailored to this
project are noted at:

<https://www.mediawiki.org/wiki/Reading/Web/Coding_conventions>

URL query parameters
--------------------

- `useskinversion`: Like `useskin` but for overriding the Vector skin version
  user preference and configuration. E.g.,
  http://localhost:8181?useskin=vector&useskinversion=2.

Skin preferences
----------------

Vector defines skin-specific user preferences. These are exposed on
Special:Preferences when the `VectorShowSkinPreferences` configuration is
enabled. The user's preference state for skin preferences is used for skin
previews and any other operation unless specified otherwise.

### Version

Vector defines a "version" preference to enable users who prefer the December
2019 version of Vector to continue to do so without any visible changes. This
version is called "Legacy Vector." The related preference defaults are
configurable via the configurations prefixed with `VectorDefaultSkinVersion`.
Version preference and configuration may be overridden by the `useskinversion`
URL query parameter.

### Pre-commit tests

A pre-commit hook is installed when executing `npm install`. By default, it runs
`npm test` which is useful for automatically validating everything that can be
in a reasonable amount of time. If you wish to defer these tests to be executed
by continuous integration only, set the `PRE_COMMIT` environment variable to `0`:

```bash
$ export PRE_COMMIT=0
$ git commit
```

Or more succinctly:

```bash
$ PRE_COMMIT=0 git commit
```

Skipping the pre-commit tests has no impact on Gerrit change identifier hooks.

### Build product diffs, merging, and rebasing

A temporary Git attributes file is provided to improve the experience of working with versioned
build products:

-   Diffs are treated as binaries. This a single status line will be reported for any deltas as they
    may be lengthy. This default can be overridden by passing `-a` to `git diff`.
-   Merge and rebase confictscan be overridden by executing `git config merge.ours.driver true`
    once. This means that you will be responsible for manually calling `npm run build` without a
    reminder. This may be preferable to calling `git reset resources/dist` to manually override.

### Hooks

See [hooks.txt](hooks.txt).
