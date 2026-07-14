# Contributing

## The `berlindb/core` dependency

This plugin depends on `berlindb/core`. How composer finds it differs by context:

- **Default (committed here):** a VCS repository pointing at GitHub, requiring
  `dev-master`, so a fresh clone builds with `composer install` and no local checkout.
  Once core tags a release on Packagist, this can become a plain version constraint
  with no `repositories` block at all.

- **Local dev against a working copy of core:** override the repository with a `path`
  repo that symlinks a sibling checkout, so edits to core are picked up immediately.
  This is a local-only change - do not commit it:

  ```bash
  composer config repositories.berlindb-core path ../path/to/berlindb-core
  composer update berlindb/core
  # restore the committed VCS repo when done:
  git checkout composer.json && composer update berlindb/core
  ```

- **CI** overrides the repository at run time to the core ref under test (for the
  cross-repo canary, the exact commit that triggered it) - see `.github/workflows/tests.yml`.

## Running the tests

Needs a MySQL/MariaDB server and the WordPress test suite:

```bash
composer install
bin/install-wp-tests.sh wordpress_test root '' 127.0.0.1 latest
composer test
```

## What the tests guard

- **`SchemaDriftTest`** diffs each declared schema against the live WordPress table
  (via BerlinDB's `Schema::from_table()` + `diff()`), so a WordPress version that
  changes a core table turns the suite red. This is why the CI matrix spans WordPress
  versions.
- **`QueriesTest`** exercises reads, `belongs_to` / `has_many` relationships, the
  meta-type mapping, and a taxonomy filter through the `term_relationships` junction.

## The cross-repo canary

`berlindb/core` can validate every commit against this plugin automatically: copy
`.github/canary-dispatch.example.yml` into core as a workflow. On push to core master
it dispatches a `core-updated` event here, and this suite runs against that exact core
commit.
