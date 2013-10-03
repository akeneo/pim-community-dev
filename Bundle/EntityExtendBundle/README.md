EntityExtendBundle
==================
- Allows to add an additional fields into existing entities through UI or using configuration files
- Allows to add new entities through UI or using configuration files

UI
--
To manage existing entities or create new ones through UI go to System > Entities section. On this page you can see a list of all entities, but please note that you can modify only entities marked as extendable. See IS EXTEND column to see whether an entity can be extended or not. To create a new entity click **Create entity** button at the top right corner of the page, fill the form and click **Save And Close**. Next add necessary fields to you entity clicking on **Create field** button. To add new field to existing entity go to view page of this entity and click **Create field** button. When all changes are made do not forget to click Update schema to apply your changes.

Config files
------------
The following example shows how existing entity can be extended or new one can be created.
``` yaml
# add field to Contact entity
OroCRM\Bundle\ContactBundle\Entity\Contact:
    fields:
        my_field:
            type:                   string

# create new entity
Country:
    is_extend:                      true                    # set true to allow to add other fields in other bundles
    configs:                                                # set default values for metadata
        entity:
            label:                  Country
            plural_label:           Countries
    fields:                                                 # declare fields
        id:
            type:                   int
        name:
            type:                   string
            configs:
                entity:
                    label:          Name
            options:
                length:             200
```

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
