EntityExtendBundle
==================
- Allows to add an additional fields into existing entities through UI
- Allows to add new entities through UI

Initializing entity extend functionality
----------------------------------------
Before entity extend functionality can be user you need to load all necessary data into the database. To achieve this the following command can be used:
```bash
php app/console oro:entity-extend:init
```

Warming up the cache
--------------------
The following command prepares extended entities cache:
```bash
php app/console oro:entity-extend:update-config
```

Saving entity extend configuration
----------------------------------
To save entity extend configuration stored in the database to the application cache, the following command can be used:
```bash
php app/console oro:entity-extend:dump
```

Clearing up the cache
-------------------------------
The following command removes all data related to entity extend functionality from the application cache:
```bash
php app/console oro:entity-extend:clear
```

Backing up entity data
----------------------
The following command can be used to backup data of particular entity:
```bash
php app/console oro:entity-extend:backup [entity class name] [backup path]
```
This command has two arguments:
 - entity class name - It is required. It is used to specify which entity need to be backed up.
 - backup path - It is optional. Using this argument you can specify a folder where this command will store backed up data. If this argument is omitted the data will be stored in a folder specified in oro_entity_extend.backup parameter. The value of this parameter can be changed in application configuration file.

By now backup is supported for MySql and Postgres databases only.
