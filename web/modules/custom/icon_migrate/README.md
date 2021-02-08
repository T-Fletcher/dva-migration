# Icon Migrate
## For The Department of Veterans' Affairs
Version: 1.1, February 2021

## Prerequisite

### Module Dependencies
**Be sure the following modules are enabled:**
 - config
 - migrate_plus
 - migrate_drupal
 - migrate_tools
 - migrate_upgrade
 - media_migration
 - smart_sql_idmap
 - field_group
 - field_group_migrate
 - media_migration (patched, included in this repo)
 
## Settings

Update the source site database details in ``migrate_plus.migration_group.icon_migrate.yml`` If you are using Docker, the database can be reached locally at IP `0.0.0.0`. To find the port number, execute command ``docker ps`` and locate the host port that is mapped to ``3306``.

In your target site's settings.php, enter the source database details

```php
$databases['migrate']['default'] = array (
     'database' => 'SOURCE_DB_NAME',
     'username' => 'SOURCE_DB_USER',
     'password' => 'SOURCE_DB_PASS',
     'prefix' => '',
     'host' => 'localhost',
     'port' => '3306',
     'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
     'driver' => 'mysql',
   );
```   

Be sure the source site's file system settings (http://your.site/admin/config/media/file-system) are correct - the path to file is correctly set and all files can be opened from the pages on the source site.

In ``migrate_plus.migration.upgrade_d7_files.yml``, be sure to enter the absolute path to the source Drupal site's *docroot* directory. The source and destination sections should look like:

```yaml
source:
  plugin: d7_file
  scheme: public
  constants:
    source_base_path: /path/to/source/drupal7
```
```angular2html
destination:
  plugin: 'entity:file'
  source_base_path: /path/to/source/drupal7/sites/default/files/
```
### Memory limit
PHP CLI memory limit needs to be at least 512MB. Use the command below to find out which ```php.ini``` file is used by CLI and change the memory limit in that file accordingly:

```php -i | grep "php.ini"```

## Import Migration Config

**NOTE:** The config files imported from the ``icon_migrate`` module represents a subset of all the available migrations Drupal 8 can use. We only care about certain items after all.

Before the migration can be run, the files containing the instructions must be imported into the D8 database. These must be imported again every time any changes are made to the files!

Run the following drush command to *import* migration configuration

``drush config-import --partial --source=modules/custom/icon_migrate/config/install``

## Migrate Site Configuration

**NOTE: Migrating configuration need only be done once**. Once you have run the migrations once and the config is stable, it's only really the content that will need refreshing.

Run the following drush command to *migrate* the configuration of items to be migrated

``drush migrate:import --group=icon_migrate --tag=Configuration --execute-dependencies --continue-on-failure``

You will see many errors in the output, they are largely due to modules that are missing in and/or imcompatible with Drupal 8. The ```--continue-on-failure``` flag allows the process to continue so compatible items can still be migrated. 

## Migrate Site Content

After the configuration of migratable items, run the following commands to execute the actual content migration.

**Migrate from here on only, once the config has been migrated once and the D8 site config is stable**

_The order is important, as many entities depend on the existence of others to work._

### 1 Assets

We will first migrate assets that are not nodes, for example, files and taxonomy terms. This will allow migrated node to later make correct references to them.

``drush migrate:import --group=icon_migrate --tag=Asset --execute-dependencies --continue-on-failure``

### 2 Nodes

We are then ready to migrate nodes:

``drush migrate:import --group=icon_migrate --tag=Node --execute-dependencies``

Once the nodes have migrated, if they all appear to live in the root of the site (`/mypage` instead of `/some-path/mypage`):

1. Roll back the url alias migration: `drush migrate:rollback upgrade_d7_url_alias`
2. Visit the URL Alias screen and bulk-delete all aliases (`/admin/config/search/path`)
3. Run the URL alias migration again: `drush migrate:import upgrade_d7_url_alias`

This will clean out root-level paths that nodes may add as Aliases, and fix missing breadcrumbs. 

### 3 Menus

After nodes are migrated, we are ready to migrate menus:

``drush migrate:import --group=icon_migrate --tag=Menu --execute-dependencies``

**NOTE:** Menus can be migrated, but the links within are stored in the database and are not captured in config exports. Any customisations will need to be done again anytime this migration is run.

Menus must be added after Nodes, as the links will not be migrated if they don't lead somewhere. Rolling back and re-running the migration can also help restore missing menu positions for migrated nodes. 

### 4 Blocks

See README inside the *icon_migrate_block* module.

## Helpful command
Run the following drush command to review all available configured migrations

``drush ms | grep icon_migrate``
This command should also display the status of the migration such as the number of items in the source that can be migrated

## Known issues

### 1. Admin menu
The Admin menu will be messed up after the migration as the script will merge the source site's admin menu with that of the destination site. A quick manual fix is to delete all those source site's duplicated links from ``https://your.site/admin/structure/menu/manage/admin`` Those migrated items will have the delete action next to them.

### 2. Configuration page
There is a core bug that will prevent the Configuration page ``/admin/config`` from being loaded. This has been discussed on https://www.drupal.org/project/drupal/issues/3106659 and a patch is provided in comment #12 https://www.drupal.org/project/drupal/issues/3106659#comment-13578709 

### 3. Structure page
Due to clashing menu names, the Structure page will show no menu links until the duplicate links are manually removed (``/admin/structure/menu/manage/admin``)
