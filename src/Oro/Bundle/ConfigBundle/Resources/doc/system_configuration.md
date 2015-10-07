## System configuration UIX ##
### Configuration Form Definition ###

Config form definitions should be defined in system_configuration.yml file in any bundle.
Root node should be `oro_system_configuration`

####Available nodes:####
- `groups`    - definition of field groups. More [details](#groups)
- `fields`    - definition of field (form type). More [details](#fields)
- `tree`      - definition of configuration form tree. More [details](#tree)

#### Groups
This node should be also declared under root node and contains array of available field groups with its properties
Group is abstract fields bag, view representation of group managed on template level of specific configuration template
and dependent on its position in tree.
This means that group could be rendered as fieldset or tab or like part of accordion list.

```
oro_system_configuration:
    groups:
        platform: #unique name
            title: 'Platform' # title is required
            icon:  icon-hdd
            priority: 30      # sort order
```

Groups definitions will be replaced recursive from configs that will parse after original definition.
So way to override existed group title is just to redefine group with the same name and `title` value
```
oro_system_configuration:
    groups:
        platform:
            title: 'New title' # overridden title
```
#### Fields
Field declaration have required property `type`.
`type` - refers to form type of which field should be created
`tooltip` - show additional info about field
`acl_resource` - determines acl resource to check permissions to change config field value(optional)
`priority` - sort order for displaying(optional)

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
            tooltip: 'Some additional information'
            acl_resource: 'acl_resource_name'
            priority: 20
```
#### Tree
Configuration form tree makes definition of nested form elements.
Tree name should be unique to prevent merge of content from another trees.
All nested elements of the group should be placed under "children" node.
Sort order can be set with "priority" property
```
oro_system_configuration:
    tree:
        tree_name:
            group1:
                priority: 20
                children:
                    some_group2:
                        children:
                            some_group3:
                                - some_field
                                ...
                                - some_another_field
```
