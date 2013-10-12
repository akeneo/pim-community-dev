Sorter extension:
=======

Overview
--------
This extension provides sorting, also it responsible for passing "sorter" settings to view layer.
Now implemented only sorter for ORM datasource.

Settings
---------
Pager setting should be placed under `sorters` tree node.

```
datagrid:
    demo:
        source:
            type: orm
            query:
                select
                    - o.label
                    - 2 as someAlias
                from:
                    - { table: SomeBundle:SomeEntity, alias: o }

        columns:
            label:
                type: field

            someColumn:
                type: fixed
                value_key: someAlias

        ....

        sorters:
            columns:
                label:  # column name for view layer
                    data_name: o.label   # property in result set (column name or alias), if main entity has alias
                                         # like in this example it will be added automatically
                someColumn:
                    data_name: someAlias
            default:
                label: %oro_grid.extension.orm_sorter.class%::DIRECTION_DESC # sorters enabled by default, key is a column name

            enable_multisort: true|false # is multisorting mode enabled ? False by default

```
