## System configuration UIX ##
### Configuration Form Definition ###

Config form definitions should be defined in system_configuration.yml file in any bundle.
Root node should be `oro_system_configuration`

####Available nodes:####
- `levels`    - definition of available config levels. More [details](#levels)
- `groups`    - definition of field groups. More [details](#groups)
- `fields`    - definition of field (form type). More [details](#fields)
- `tree`      - definition of configuration form tree. More [details](#tree)
- `tags`      - definition of tags tree. More [details](#tags)

#### Levels
Levels node should be declared under root node and contains array of available config levels.
This node will be merged from all defined configs to one array with unique values.
```
oro_system_configuration:
    levels:
       - global
```
#### Groups
This node should be also declared under root node and contains array of available field groups with its properties
Group is abstract fields bag, view representation of group managed on template level of specific configuration template
and dependent on its position in tree.
This means that group could be rendered as fieldset or 1tab or like part of accordion list.

```
oro_system_configuration:
    groups:
        platform: #unique name
            label: 'Platform' # label is required
            icon:  icon-hdd
```

Groups definitions will be replaced recursive from configs that will parse after original definition.
So way to override existed group label is just to redefine group with the same name and `label` value
```
oro_system_configuration:
    groups:
        platform:
            label: 'New label' # overridden label
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
        group1:
            some_group2:
                some_group3:
                    - some_field
                    ...
                    - some_another_field
```
#### Tags
Tags is one level tree that allows to group similar fields into small logical groups.
This declaration maybe useful when developer should provide a way to change some configuration values in modal window
```
oro_system_configuration:
    tree:
        group1:
            some_group2:
                some_group3:
                    - some_field
                    ...
                    - some_another_field
```
