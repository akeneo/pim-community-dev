Datagrid
--------

Datagrid is a main entity that contains fields, additional entities, DB query and parameters, process it and returns results - data that will be rendered on UI.

#### Class Description

* **Datagrid \ DatagridInterface** - basic datagrid interface, that provides additional methods to work with sorters, actions, router and names.
* **Datagrid \ ResultRecordInterface** - basic interface for Result Record entity;
* **Datagrid \ Datagrid** - Datagrid entity implementation of Datagrid interface, implements all methods and has protected methods to apply additional entities parameters to DB request and bind source parameters;
* **Datagrid \ DatagridView** - entity that encapsulates all data required for Datagrid view.
* **Datagrid \ ResultRecord** - implementation of Result Record interface that supports data extracting from embedded objects.

#### Configuration

```
parameters:
    oro_grid.datagrid.class: Oro\Bundle\GridBundle\Datagrid\Datagrid
```
