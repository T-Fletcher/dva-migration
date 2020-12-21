# Drupal 7 to 8 media migration

This module implements a migration path for Drupal 7 sites using media module to
Drupal 8|9.

It performs the following:

* For media bundles which exist in the destination, it creates and attaches its
  fields along with their configuration.
* Maps Drupal 7 file entities to their respective media ones in content
  migrations.
* Transforms image fields to media image fields.
* Transforms Media WYSIWYG's `media_filter` tokens into `entity_embed` or
  `media_embed` ones.

## Configuration

* Media Migration transforms Media WYSIWYG embed tokens to embed code HTML tags.
  There are two destination filter plugins supported: `entity_embed` from the
  Entity Embed contrib module, and `media_embed` from Drupal 8|9 core's Media
  module.

  The transformation destination filter plugin can be specified in the
  destination site's `settings.php` file:

  ```
  $settings['media_migration_embed_token_transform_destination_filter_plugin'] =
  'media_embed[|entity_embed]';
  ```

  If this setting is not defined, then the destination filter plugin ID defaults
  to `entity_embed` for BC reasons. This also happens when you're on Drupal core
  8.7.x, becasue the `media_embed` filter plugin was introduced in 8.8.x.

* By default (for BC reasons), after the embedded media token transform, the
  entities are referenced by their ID. This can be changed to UUID by adding the
  following to `settings.php`:

  ```
  $settings['media_migration_embed_media_reference_method'] = 'uuid';
  ```

  This reference method configuration is evaluated only if the destination
  filter is `entity_embed`, since the `media_embed` filter plugin can refer
  media entities only by their UUID.

  Entities in Drupal 7 may have UUIDs if the https://www.drupal.org/project/uuid
  contrib module is installed. Media Migration does not migrate file UUIDs to
  Media UUIDs. We are convinced that the file's UUID belongs to the file, and we
  don't want to use the same UUID for two different entities.

## Media WYSIWYG

If the source site uses Media WYSIWYG to embed media, this module will transform
its tokens into `entity_embed` or `media_embed` ones.

If you want to transform embed tokens to `entity_embed` ones, you will need to
install the Entity Embed module.

You also have to create and/or configure the custom media view modes __with the
same machine names__ that the source site used BEFORE running the migration.

## Usage

The following requires the `migrate_upgrade` module.

1. Require this module via composer so it downloads all of its dependencies.

```bash
composer require drupal/media_migration
```

2. Install it.

```bash
drush pm:enable media_migration --yes
```

3. Recreate migration files and export them. The `legacy-db-key` is the key
of the database connection of the source database (the Drupal 7 one).

```bash
drush migrate:upgrade --legacy-db-key=drupal7 --legacy-root=sites/default/files --configure-only
```

4. Run configuration migrations:

```bash
drush migrate:import --group=migrate_drupal_7 --tag=Configuration --execute-dependencies
```

The result of the above command should create and attach the Drupal 7 fields to
the media entities,create entity reference fields pointing to media entities
instead of image fields, and prepare the content migrations.

5. Run content migration:

```bash
drush migrate:import --group=migrate_drupal_7 --tag=Content --execute-dependencies
```

The result of the above command should be all the media content migrated into
media entities plus any `media_wysiwyg` tokens transformed into `entity_embed`
or `media_embed` ones.
