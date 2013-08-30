## System configuration UIX ##
### Configuration Form Definition ###

Config form definitions should be defined in system_configuration.yml file in any bundle.
Root node should be `oro_system_configuration`

####Available nodes:####
- `levels`    - definition of available config levels. More [details](#levels)
- `htabs`     - definition of horizontal tabs. More [details](#tabs)
- `vtabs`     - definition of vertical tabs. More [details](#tabs)
- `fieldsets` - definition of fields group. More [details](#fieldsets)
- `fields`    - definition of field (form type). More [details](#fields)
- `tree`      - definition of configuration form tree. More [details](#tree)

#### Levels
Levels node should be declared under root node and contains array of available config levels.
This node will be merged from all defined configs to one array with unique values.
```
oro_system_configuration:
    levels:
       - global
```
#### Tabs
This node should be also declared under root node and contains array of available tabs with properties.
```
oro_system_configuration:
    htabs:
        platform: #unique name
            label: 'Platform' # label is required
            icon:  icon-hdd
    vtabs:
        ...
        ...
```

Tabs definitions will be replaced recursive from configs that will parse after original definition.
So way to override existed tab label is just to redefine tab with the same name and `label` value
```
oro_system_configuration:
    htabs:
        platform:
            label: 'New label' # overridden label
```
#### Fieldsets
Fielset declaration is similar to the tabs declaration, but only `label` is allowed here and it's required
    fieldsets:
```
oro_system_configuration:
    fieldsets:
        locale_options: #unique name
            label: 'Locale options'
```
#### Fields
Field declaration have 2 required properties `type` and `levels`.
`type` - refers to form type of which field should be created
`levels` - what config levels representations should show field

Also `options` available property here, it's just a proxy to form type definition

**Example**
```
oro_system_configuration:
       fields:
        date_format:
            type: text # can be any custom type
            options:
               label: 'Date format'
               # here we can override any default option of the given form type
               # also here can be added field tooltips
            levels: [ global ]
```
#### Tree
Configuration form tree makes definition of nested form elements.
```
oro_system_configuration:
    tree:
        some_htab_name:
            some_vtab_name:
                some_fieldsets:
                    - some_field
                    ...
                    - some_another_field
```
