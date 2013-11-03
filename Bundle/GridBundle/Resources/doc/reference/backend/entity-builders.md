Entity Builders
---------------

Entity Builders provides functionality to build specific types of service entities for Datagrids and Datagrid Managers.

#### Class Description

* **Builder \ DatagridBuilderInterface** - basic interface for Datagrid Builder, provides getter for Datagrid entity and methods to inject additional service entities (filters, sorters, row actions);
* **Builder \ AbstractDatagridBuilder** - abstract implementation of DatagridBuilder interface, receives form and additional entities factories to create entity instances;
* **Builder \ ORM \ DatagridBuilder** - extends abstract Datagrid Builder, creates ORM Pager entity;
* **Builder \ ListBuilderInterface** - basic interface to build Field Description entities and add it to Field Collection;
* **Builder \ ListBuilder** - implements List Builder interface and all its methods.

#### Configuration

```
parameters:
    oro_grid.builder.datagrid.class: Oro\Bundle\GridBundle\Builder\ORM\DatagridBuilder
    oro_grid.builder.list.class:     Oro\Bundle\GridBundle\Builder\ListBuilder

services:
    oro_grid.builder.datagrid:
        class:     %oro_grid.builder.datagrid.class%
        arguments:
            - @form.factory
            - @event_dispatcher
            - @oro_grid.filter.factory
            - @oro_grid.sorter.factory
            - @oro_grid.action.factory
            - %oro_grid.datagrid.class%

    oro_grid.builder.list:
        class:     %oro_grid.builder.list.class%
```
