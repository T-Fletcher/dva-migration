# Icon Migrate
## For The Department of Veterans' Affairs
Version: 1.0, December 2020

## Prerequisite

### Module Dependencies
**Be sure the following modules are enabled:**
 - config
 - migrate_plus
 - migrate_drupal
 - migrate_tools
 - migrate_upgrade 
 - media_migration (patched)
 - icon_migrate_block
 
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

Run the following drush command to *import* migration configuration

``drush config-import --partial --source=modules/custom/icon_migrate/config/install``

## Migrate Site Configuration

Run the following drush command to *migrate* the configuration of items to be migrated

``drush migrate:import --group=icon_migrate --tag=Configuration --execute-dependencies --continue-on-failure``

You will see many errors in the output, they are largely due to modules that are missing in and/or imcompatible with Drupal 8. The ```--continue-on-failure``` flag allows the process to continue so compatible items can still be migrated. 

## Migrate Site Content

After the configuration of migratable items, run the following commands to execute the actual content migration:

### 1 Assets

We will first migrate assets that are not nodes, for example, files and taxonomy terms. This will allow migrated node to later make correct references to them.

``drush migrate:import --group=icon_migrate --tag=Content --execute-dependencies --continue-on-failure``

### 2 Nodes

We are then ready to migrate nodes:

``drush migrate:import --group=icon_migrate --tag=Node --execute-dependencies``

### 3 Menus

After nodes are migrated, we are ready to migrate menus:

``drush migrate:import --group=icon_migrate --tag=Menu --execute-dependencies``

### 4 Blocks

See README inside the *icon_migrate_block* module.

## Helpful command
Run the following drush command to review all available configured migrations

``drush ms | grep icon_migrate``
This command should also display the status of the migration such as the number of items in the source that can be migrated

## Known issues

### 1. Admin menu
The Admin menu will be messed up after the migration as the script will merge the source site's admin menu with that of the destination site. A quick manual fix is to delete all those source site's duplicated links from ``https://your.site/admin/structure/menu/manage/admin`` Those migrated items will have the delete action next to them.

### 2. Media
There is a core bug that will prevent the Configuration page ``/admin/config`` from being loaded. This has been discussed on https://www.drupal.org/project/drupal/issues/3106659 and a patch is provided in comment #12 https://www.drupal.org/project/drupal/issues/3106659#comment-13578709 
