OroEntityBundle
========================

- [Form Components Overview](./Resources/doc/form_components.md)


    Example for Resources/config/entity_extend.yml
    TestClassExtend:
        configs:
            entity:
                label:                  TestClassExtend
        fields:
            testStringField:
                type:                   string
                configs:
                    entity:
                        label:          testStringField
                options:
                    length:             200
            testIntegerField:
                type:                   smallint
            testHiddenField:
                mode:                   hidden
                type:                   string
            testReadonlyField:
                mode:                   readonly
                type:                   string

    Oro\Bundle\UserBundle\Entity\User:
        fields:
            testField:
                type:                   string
            testHiddenField:
                mode:                   hidden
                type:                   string
            testReadonlyField:
                mode:                   readonly
                type:                   string
                
**Entity Manager**

In order to extend some native Doctrine Entity Manager functionality a new class `OroEntityManager` was implemented.
In case any other modification are required, your class should extend `OroEntityManager` instead of Doctrine Entity Manager.

**Filter Collection**

Standard Doctrine filter collection implementation allows to add/enable sql filter by passing class name only.
It makes impossible to inject custom services into filters. To provide this functionality,
a new `FilterCollection` class was implemented that allows to add filter objects directly.

Necessary filters can be automatically added to the filters collection by adding `oro_entity.orm.sql_filter` tag:

```yml
oro_security.orm.ownership_sql_filter:
    class: %oro_security.orm.ownership_sql_filter.class%
    arguments:
       - @doctrine.orm.entity_manager
    tags:
       - { name: oro_entity.orm.sql_filter, filter_name: ownershipFilter, enabled: true }
```

where

 - **filter_name** - required filter name,
 - **enbaled** - flag, if the filter must be enabled, by default filters are disabled


