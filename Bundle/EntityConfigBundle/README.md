EntityConfigBundle
==================
- Allows to add metadata (configuration) to any entity class
- Provides functionality to manage this metadata

Get Started
-----------
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
This configuration adds 'demo_attr' attribute with 'Demo' value to all configurable entities. The configurable entity is an entiry marked with @Config annotation. Also this code automatically adds a servive named **oro_entity_config.provider.acme** into DI container. You can use this service to get a value of 'demo_attr' attribute for particular entity. For example:
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
                        filter_type:        oro_grid_orm_string
                        sortable:           true                 # allows an administrator to sort rows clicks on 'Demo Attr' column
                    form:
                        type:               text                 # sets the attribute type
                        block:              entity               # specifies in which block on the form this attribute should be displayed
                        options:
                            label:          'Demo Attr'          # sets the the label name
```
Now you may go to System > Entities. The 'Demo Attr' column should be displayed in the grid. Click Edit on any entity to go to edit entity form. 'Demo Attr' field should be displayed there.

Config Parts
------------
- Config - it is key-value storage
- ConfigId - resource Id it is identifier for some resources(Entity, Field)
- ConfigManager - config manager
- ConfigProvider - get config form configManger filtered by scope add has helpful function to manage

Start working
-------------
add entity_config.yml file  to the "Resource" folder of bundle
```
oro_entity_config:
    extend:                                 #scope name
        entity:                             #entities property
                owner:
                    options:
                        priority:           40
                        internal:           true
                        default_value:      'System'
                    grid:
                        type:               string
                        label:              'Type'
                        filter_type:        oro_grid_orm_string
                        required:           true
                        sortable:           true
                        filterable:         true
                        show_filter:        true
                    form:
                        type:               text
                        block:              entity
                        options:
                            read_only:      true
                            required:       false
                            label:          'Type'
```    

Use in Code
-----------
You can manage your all configuration data in some scope through ConfigProvider.
The configuration provider it is a service with name "oro_entity_config.provider" + scope
For example the following code gets the configuration provider for 'extend' scope.
``` php
<?php

/** @var ConfigProvider $configProvider */
$configProvider = $this->get('oro_entity_config.provider.extend');
```

Provider function
-----------------
- isConfigurable($className)
- getId($className, $fieldName = null)
- hasConfig($className, $fieldName = null)
- getConfig($className, $fieldName = null)
- getConfigById($configid)
- createConfig($configId, array $values)
- getIds($className = null)
- getConfigs($className = null)
- map(\Closure $map, $className = null)
- filter(\Closure $map, $className = null)
- getClassName($entity/PersistColection/$className)
- clearCache($className, $fieldName = null)
- persist($config)
- merge($config)
- flush()

Config function
-----------------
- getId()
- get($code, $strict = false)
- set($code, $value)
- has($code)
- is($code)
- all(\Closure $filter = null)
- public function setValues($values)

ConfigManager function
----------------------
- getConfigChangeSet($config)

Events
------
- Events::NEW_ENTITY_CONFIG_MODEL
- Events::NEW_FIELD_CONFIG_MODEL
- Events::PRE_PERSIST_CONFIG

