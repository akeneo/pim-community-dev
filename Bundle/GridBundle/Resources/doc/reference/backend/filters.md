Filters
-------

Filters allows to apply additional conditions to DB request and show in grid only required rows. Filter entities are created by Filter Factory.

#### Class Description

* **Filter \ FilterInterface** - basic interface for Grid Filter entities;
* **Filter \ ORM \ AbstractFilter** - abstract implementation of Filter entity;
* **Filter \ ORM \ NumberFilter** - ORM filter for number values;
* **Filter \ ORM \ StringFilter** - ORM filter for string values;
* **Filter \ ORM \ ChoiceFilter** - ORM filter which allows to use choices (single or multiple);
* **Filter \ ORM \ EntityFilter** - ORM choices filter based on Symfony entity field type and allows to use
entity repository or query builder as choices data source;
* **Filter \ ORM \ BooleanFilter** - ORM filter which allows to filter data as boolean value;
* **Filter \ ORM \ AbstractDateFilter** - abstract filter implementation to work with date/datetime values;
* **Filter \ ORM \ DateRangeFilter** - ORM filter for date and date range values;
* **Filter \ ORM \ DateTimeRangeFilter** - ORM filter for datetime and datetime range values;
* **Filter \ FilterFactoryInterface** - basic interface for Filter Factory entity;
* **Filter \ FilterFactory** - basic implementation of Filter Factory entity to create Filter entities.

#### Configuration

**Configuration of Services**

```
parameters:
    oro_grid.filter.factory.class: Oro\Bundle\GridBundle\Filter\FilterFactory

services:
    oro_grid.filter.factory:
        class:     %oro_grid.filter.factory.class%
        arguments: ["@service_container", ~]
```

**Configuration of Filter Types**

```
services:
    oro_grid.orm.filter.type.date_range:
        class: Oro\Bundle\GridBundle\Filter\ORM\DateRangeFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_date_range }

    oro_grid.orm.filter.type.datetime_range:
        class: Oro\Bundle\GridBundle\Filter\ORM\DateTimeRangeFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_datetime_range }

    oro_grid.orm.filter.type.number:
        class:     Oro\Bundle\GridBundle\Filter\ORM\NumberFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_number }

    oro_grid.orm.filter.type.string:
        class:     Oro\Bundle\GridBundle\Filter\ORM\StringFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_string }

    oro_grid.orm.filter.type.choice:
        class:     Oro\Bundle\GridBundle\Filter\ORM\ChoiceFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_choice }

    oro_grid.orm.filter.type.select:
        class:     Oro\Bundle\GridBundle\Filter\ORM\SelectFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_select }

    oro_grid.orm.filter.type.boolean:
        class:     Oro\Bundle\GridBundle\Filter\ORM\BooleanFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_boolean }

    oro_grid.orm.filter.type.entity:
        class:     Oro\Bundle\GridBundle\Filter\ORM\EntityFilter
        arguments: ["@translator"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_entity }
```
