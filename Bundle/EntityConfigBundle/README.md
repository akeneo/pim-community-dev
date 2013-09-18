EntityConfigBundle
==================
- Provide functionality to manage config for some resource
- Config it is meta info about resources (Config is Metadata)
- backend configuration and user interface configuration

Config Parts
------------
- Config - it is key-value storage
- ConfigId - resource Id it is identifier for some resources(Entity, Field)
- ConfigManager - config mananger
- ConfigProvider - get config form configManger filtred by scope add has helpfull function to manage

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
You manage your config(scope) through ConfigProvider 
Config provider it is a service with name "oro_entity_config.provider" + scope

```
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

