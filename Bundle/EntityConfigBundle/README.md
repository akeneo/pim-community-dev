EntityConfigBundle
==================
- Allows to add metadata (configuration) to any entity class
- Provides functionality to manage this metadata

Getting Started
---------------
To show how metadata can be added to an entity lets add the following YAML file (this file must be located in [BundleName]\Resources\config\entity_config.yml):
``` yaml
oro_entity_config:
    acme:                                                        # a configuration scope name
        entity:                                                  # a section describes en entity
            items:                                               # starts a description of entity attributes
                demo_attr:                                       # adds an attribute named 'demo_attr'
                    options:
                        default_value:      'Demo'               # sets the default value for 'demo_attr' attribute
```
This configuration adds 'demo_attr' attribute with 'Demo' value to all configurable entities. The configurable entity is an entity marked with @Config annotation. Also this code automatically adds a service named **oro_entity_config.provider.acme** into DI container. You can use this service to get a value of 'demo_attr' attribute for particular entity.
To apply this changes execute **oro:entity-config:update** command:
```bash
php app/console oro:entity-config:update
```
An example how to get a value of a configuration attribute:
``` php
<?php
    /** @var ConfigProvider $acmeConfigProvider */
    $acmeConfigProvider = $this->get('oro_entity_config.provider.acme');

    // retrieve a value of 'demo_attr' attribute for 'AcmeBundle\Entity\SomeEntity' entity
    // the value of $demoAttr variable will be 'Demo'
    $demoAttr = $acmeConfigProvider->getConfig('AcmeBundle\Entity\SomeEntity')->get('demo_attr');
```
If you want to set a value different than the default one for some entity just write it in @config annotation for this entiry. For example:
``` php
<?php
/**
 * @ORM\Entity
 * @Config(
 *  defaultValues={
 *      "acme"={
 *          "demo_attr"="MyValue"
 *      }
 *  }
 * )
 */
class MyEntity
{
    ...
}
```

The result is demonstrated in the following code:
``` php
<?php
    /** @var ConfigProvider $acmeConfigProvider */
    $acmeConfigProvider = $this->get('oro_entity_config.provider.acme');

    // retrieve a value of 'demo_attr' attribute for 'AcmeBundle\Entity\SomeEntity' entity
    // the value of $demoAttr1 variable will be 'Demo'
    $demoAttr1 = $acmeConfigProvider->getConfig('AcmeBundle\Entity\SomeEntity')->get('demo_attr');

    // retrieve a value of 'demo_attr' attribute for 'AcmeBundle\Entity\MyEntity' entity
    // the value of $demoAttr2 variable will be 'MyValue'
    $demoAttr2 = $acmeConfigProvider->getConfig('AcmeBundle\Entity\MyEntity')->get('demo_attr');
```
Basically it is all you need to add metadata to any entity. But in most cases you want to allow an administrator to manage your attribute in UI. To accomplish this lets change our YAML file in the following way:
``` yaml
oro_entity_config:
    acme:                                                        # a configuration scope name
        entity:                                                  # a section describes en entity
            items:                                               # starts a description of entity attributes
                demo_attr:                                       # adds an attribute named 'demo_attr'
                    options:
                        default_value:      'Demo'               # sets the default value for 'demo_attr' attribute
                    grid:                                        # configure a data grid to display 'demo_attr' attribute
                        type:               string               # sets the attribute type
                        label:              'Demo Attr'          # sets the data grid column name
                        show_filter:        true                 # the next three lines configure a filter for 'Demo Attr' column
                        filterable:         true
                        filter_type:        string
                        sortable:           true                 # allows an administrator to sort rows clicks on 'Demo Attr' column
                    form:
                        type:               text                 # sets the attribute type
                        options:
                            block:          entity               # specifies in which block on the form this attribute should be displayed
                            label:          'Demo Attr'          # sets the the label name
```
Now you may go to System > Entities. The 'Demo Attr' column should be displayed in the grid. Click Edit on any entity to go to edit entity form. 'Demo Attr' field should be displayed there.

[Example of YAML config](Resources/doc/configuration.md)

Implementation
--------------

### ConfigId
Allows to identify each configurable object. The entity id is represented by EntityConfigId class. The field id is represented by FieldConfigId class.

### Config
The aim of this class is to store configuration data for each configurable object.

### ConfigProvider
The configuration provider can be used to manage configuration data inside particular configuration scope. Each configuration provider is a service named **oro_entity_config.provider.{scope}**, where **{scope}** is the name of the configuration scope a provider works with.
For example the following code gets the configuration provider for 'extend' scope.
``` php
<?php

/** @var ConfigProvider $configProvider */
$configProvider = $this->get('oro_entity_config.provider.extend');
```

### ConfigManager
This class is the central access point to entity configuration functionality. It allows to load/save configuration data from/into the database, manage configuration data, manage configuration data cache, retrieve the configuration provider for particular scope, and other.

### Events
 - Events::NEW_ENTITY_CONFIG_MODEL - This event is raised when a new configurable entity is found and we are going to add its metadata to the database.
 - Events::NEW_FIELD_CONFIG_MODEL - This event is raised when a new configurable entity field is found and we are going to add its metadata to the database.
 - Events::PRE_PERSIST_CONFIG - This event is raised just before new or changed configuration data is persisted in to the database.

Initialize configuration data
-----------------------------
The following command can be used to initialize all configurable entities:
```bash
php app/console oro:entity-config:init
```
This command iterates through all entities and configs files and loads entity metadata into the database.
This command is executed during the installation process and usually you do not need to execute it manually.

Update configuration data
-------------------------
The following command can be used to update configurable entities:
```bash
php app/console oro:entity-config:update
```
Usually you need to execute this command only in 'dev' mode when new new configuration attribute or whole configuration scope is added.
