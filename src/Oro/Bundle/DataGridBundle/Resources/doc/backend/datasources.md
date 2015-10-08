Datasources
============

Overview
-------------

Datagrid can retrieve data from different datasource types (e.g orm, dql, array, etc...)
Here's list of currently implemented datasource adapters:

 - [ORM](datasources/orm.md)

Datasource could be secured by adding `acl_resource` node under source tree.

Customization
-------------

To implement your own adapter you have to do following:

 - Develop class that implements DatasourceInterface
 - Register you adapter as service with tag { name: oro_datagrid.datasource, type: YOUR_ADAPTER_TYPE }

To configure which datasource grid should use just modify `type` param under source node.
For example:
```
datagrid:
    DATAGRID_NAME_HERE:
        source:
            type: YOUR_ADAPTER_TYPE
            acl_resource: SOME_RESOURCE_IF_NEEDED
```

Note: your adapter should take care about validation of it's configuration.
