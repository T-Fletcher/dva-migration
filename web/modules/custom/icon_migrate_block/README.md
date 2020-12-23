# Icon Migrate Block
Version: 1.0, December 2020

1. Enable this module to provide a beans migration plugin. 
``drush pm-enable icon_migrate_block``
This module also creates custom block types that are required by the migration.

2. Migrate the blocks by running the following command: ``drush migrate:import --group=icon_migrate_block --tag=Block``
