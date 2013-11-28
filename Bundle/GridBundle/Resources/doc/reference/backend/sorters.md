Sorters
-------

Sorter is an entity that allows to add sort conditions to DB request. Sorters are created by Sorter Factory.

#### Class Description

* **Sorter \ SorterInterface** - basic interface for Sorter entity;
* **Sorter \ ORM \ Sorter** - Sorter implementation for Doctrine ORM;
* **Sorter \ SorterFactoryInterface** - basic interface for Sorter Factory entity;
* **Sorter \ SorterFactory** - basic implementation of Sorter Factory entity to create Sorter entities.

#### Configuration

**Configuration of Services**

```
parameters:
    oro_grid.sorter.factory.class: Oro\Bundle\GridBundle\Sorter\SorterFactory

services:
    oro_grid.sorter.factory:
        class:     %oro_grid.sorter.factory.class%
        arguments: ["@service_container"]
```

**Configuration of Sorter Types**

```
parameters:
    oro_grid.sorter.class:          Oro\Bundle\GridBundle\Sorter\ORM\Sorter

services:
    oro_grid.sorter:
        class:     %oro_grid.sorter.class%
        scope:     prototype
```
