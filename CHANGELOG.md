# Changelog

All notable changes to `berlindb/wp-core-tables` are documented here. The format
follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
aims to follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- BerlinDB schemas for all core WordPress tables: `posts`, `postmeta`, `users`,
  `usermeta`, `comments`, `commentmeta`, `terms`, `term_taxonomy`,
  `term_relationships`, `termmeta`, `options`, `links`.
- A `Query` class per table (namespaced hook/table prefix `wpct_`), plus `Row`
  shapes. Meta tables share one `Row`.
- Relationships wired across the graph (unenforced `belongs_to` / `has_many`):
  post -> author / meta / comments / term_relationships, comment -> post / author,
  term -> taxonomies, term_taxonomy -> term / relationships, and the composite
  `term_relationships` junction, so taxonomy filters resolve through it.
- `get_meta_type()` overrides so meta reads map onto WordPress's native
  `post` / `user` / `comment` / `term` meta.
- Global cache-group registration for the global `users` / `usermeta` tables, so
  the object cache is scoped correctly on multisite.
- `SchemaDriftTest` - diffs every declared schema against the live WordPress table
  (`Schema::from_table()` + `diff()`), turning the suite red if a WordPress version
  changes a core table.
- `QueriesTest` - reads, relationship priming, meta-type mapping, and a taxonomy
  filter through the junction.
- `Overrides\PostsQueryOverride` (proof of concept) - an opt-in `posts_pre_query`
  interceptor that satisfies a real `WP_Query` from BerlinDB's `Posts` query instead
  of core SQL: rows come back as genuine `WP_Post` objects, `fields=ids` returns an
  ID list, and `found_posts` / `max_num_pages` report the BerlinDB total so
  pagination is correct. Doubly opt-in (call `register()` AND set the `wpct_source`
  query var); `Plugin::boot()` never enables it. Covered by `PostsQueryOverrideTest`.
- CI matrix (PHP x WordPress) plus a cross-repo canary: a push to `berlindb/core`
  master dispatches this suite against that exact core commit.

### Notes

- Requires the `the_posts` hook-prefix workaround (BerlinDB core #242) and the
  `get_meta_type()` derivation follow-up (core #243). The `wpct_` prefix keeps this
  plugin's hooks from colliding with WordPress core's until #242 lands.
