# WordPress Core Tables (BerlinDB)

![WordPress readiness](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/berlindb/wp-core-tables/master/.readiness/wordpress.json)

The **readiness** badge is the behavioral (column-flag) score from
[berlindb/readiness](https://github.com/berlindb/readiness). These schemas are authored
*on* `berlindb/core`, so 100% is expected **by construction** - it confirms the tables
are fully reproduced, and would drop only if a core flag went missing.

Registers the WordPress **core** database tables as [BerlinDB](https://github.com/berlindb/core)
3.x relations — full Schemas (columns + indexes, faithful to `wp-admin/includes/schema.php`),
plus a Query and Row per table, wired with real relationships.

It **queries** core data the BerlinDB way (relationships, `get_related`, `relation`
filtering, aggregates, the query/by-id/secondary cache model). It does **not** create,
alter, or install any table — WordPress owns them.

## Install

```bash
composer install
```

`composer.json` pulls `berlindb/core` from the local checkout via a path repository, so
the plugin always runs against the core you are developing. Activate the plugin (or just
`require vendor/autoload.php`); the `Query` classes are the API.

## Usage

```php
use WPCoreTables\Queries\Posts;

// Query wp_posts.
$posts = new Posts( array(
    'post_type__in' => array( 'post', 'page' ),
    'post_status'   => 'publish',
    'number'        => 10,
    'orderby'       => 'post_date',
    'order'         => 'DESC',
) );
foreach ( $posts->items as $post ) { /* $post is a WPCoreTables\Rows\Post */ }

// Related data (belongs_to / has_many), primed in bulk with `with`.
$posts  = new Posts( array( 'number' => 20, 'with' => array( 'author', 'meta' ) ) );
$author = $posts->get_related( $posts->items[0], 'author' ); // a Rows\User
$meta   = $posts->get_related( $posts->items[0], 'meta' );   // Rows\Meta[]

// Taxonomy: posts in a category, through the term_relationships junction.
$in_cat = new Posts( array(
    'fields'   => 'ids',
    'relation' => array(
        'name'     => 'term_relationships',
        'relation' => array(
            'name'     => 'term_taxonomy',
            'where'    => array( 'taxonomy' => 'category' ),
            'relation' => array(
                'name'  => 'term',
                'where' => array( 'slug' => 'news' ),
            ),
        ),
    ),
) );
```

## Tables

`posts` · `postmeta` · `comments` · `commentmeta` · `users` · `usermeta` · `terms` ·
`term_taxonomy` · `term_relationships` · `termmeta` · `options` · `links`.

Relationships include: `posts → author (users)`, `posts → meta / comments / term_relationships`,
`comments → post / author / meta`, `users → meta / posts`, `terms → meta / taxonomies`,
`term_taxonomy → term / relationships`, and the composite-key `term_relationships` junction.

## Design notes

- **No plugin prefix.** Core tables keep their real names, so `table_name = 'posts'`
  resolves to `{$wpdb->prefix}posts`. BerlinDB defers physical-name resolution to `$wpdb`,
  so **global** tables (`users`, `usermeta`) correctly stay base-prefixed on multisite.
- **Namespaced item names.** `item_name` is `wpct_post` (not `post`). With an empty plugin
  prefix, a bare `posts` would make BerlinDB fire `the_posts` and collide with WordPress
  core's own filter. See berlindb/core#242 (a proposed `$hook_prefix`).
- **`get_meta_type()` overrides.** Because item names are namespaced, each meta-bearing
  Query maps its meta type back to the real WP singular (`'post'`, `'user'`, ...), so the
  native item-meta path hits `wp_postmeta` etc. See berlindb/core#243.
- **Global caches.** `Plugin::boot()` registers the global tables' cache groups via
  `wp_cache_add_global_groups`, so multisite does not cache shared rows per-site.
- **Relationships are unenforced.** They declare how tables join for querying/priming; they
  emit no `FOREIGN KEY` DDL — matching how WordPress core works (no FKs) and the fact that
  the tables are never installed here.
