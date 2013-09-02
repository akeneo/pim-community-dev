## System configuration UIX ##
### Configuration Form Definition ###

Config form definitions should be defined in system_configuration.yml file in any bundle.
Root node should be `oro_system_configuration`

####Available nodes:####
- `scopes`    - definition of available config scopes. More [details](#scopes)
- `groups`    - definition of field groups. More [details](#groups)
- `fields`    - definition of field (form type). More [details](#fields)
- `tree`      - definition of configuration form tree. More [details](#tree)

#### Scopes
Scopes node should be declared under root node and contains array of available config scopes.
This node will be merged from all defined configs to one array with unique values.
```
oro_system_configuration:
    scopes:
       - global
```
#### Groups
This node should be also declared under root node and contains array of available field groups with its properties
Group is abstract fields bag, view representation of group managed on template level of specific configuration template
and dependent on its position in tree.
This means that group could be rendered as fieldset or tab or like part of accordion list.

```
oro_system_configuration:
    groups:
        platform: #unique name
            label: 'Platform' # label is required
            icon:  icon-hdd
            position: 30      # sort order
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
Field declaration have 2 required properties `type` and `scopes`.
`type` - refers to form type of which field should be created
`scopes` - what config scopes representations should show field
`acl_resource` - determines acl resource to check permissions to change config field value(optional)
`position` - sort order for displaying(optional)

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
            scopes: [ global ]
            acl_resource: 'acl_resource_name'
            position: 20
```
#### Tree
Configuration form tree makes definition of nested form elements.
Tree name should be unique to prevent merge of content from another trees.
All nested elements of the group should be placed under "children" node.
Sort order can be set with "position" property
```
oro_system_configuration:
    tree:
        tree_name:
            group1:
                position: 20
                children:
                    some_group2:
                        children:
                            some_group3:
                                - some_field
                                ...
                                - some_another_field
```
