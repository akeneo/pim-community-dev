ORM datasource
===============

Overview
--------

This datasource provide adapter to allow access data from doctrine orm using doctrine query builder.
You can configure query using `query` param under source tree. This query will be converted via YamlConverter to doctrine QueryBuilder object.

Example
-------

```
datagrid:
    DATAGRID_NAME_HERE:
        source:
            type: orm
            query:
                select:
                    - g.id
                    - g.label
                from:
                    - { table: OroCRMContactBundle:Group, alias: g }
```
