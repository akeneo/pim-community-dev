Filters
-------

Filters allows to apply additional conditions to DB request and show in grid only required rows. Filter entities are created by Filter Factory.

Flexible filters are used to apply filters to flexible attributes in flexible entities. Flexible filters has parent filters and use their basic functionality (operators, settings etc).

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
* **Filter \ ORM \ Flexible \ AbstractFlexibleFilter** - abstract ORM filter to work with flexible attributes;
* **Filter \ ORM \ Flexible \ NumberFlexibleFilter** - ORM filter to work with number flexible attributes;
* **Filter \ ORM \ Flexible \ StringFlexibleFilter** - ORM filter to work with string flexible attributes;
* **Filter \ ORM \ Flexible \ OptionsFlexibleFilter** - ORM filter to work with options flexible attributes;
* **Filter \ ORM \ Flexible \ AbstractFlexibleDateFilter** - abstract ORM filter to work with date/time flexible attributes;
* **Filter \ ORM \ Flexible \ FlexibleDateRangeFilter** - ORM filter for date flexible attribute;
* **Filter \ ORM \ Flexible \ FlexibleDateTimeRangeFilter - ORM filter for datetime flexible attribute;
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

    oro_grid.orm.filter.type.flexible_number:
        class:     Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleNumberFilter
        arguments: ["@oro_flexibleentity.registry", "@oro_grid.orm.filter.type.number"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_flexible_number }

    oro_grid.orm.filter.type.flexible_string:
        class:     Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleStringFilter
        arguments: ["@oro_flexibleentity.registry", "@oro_grid.orm.filter.type.string"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_flexible_string }

    oro_grid.orm.filter.type.flexible_date_range:
        class:     Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleDateRangeFilter
        arguments: ["@oro_flexibleentity.registry", "@oro_grid.orm.filter.type.date_range"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_flexible_date_range }

    oro_grid.orm.filter.type.flexible_datetime_range:
        class:     Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleDateTimeRangeFilter
        arguments: ["@oro_flexibleentity.registry", "@oro_grid.orm.filter.type.datetime_range"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_flexible_datetime_range }

    oro_grid.orm.filter.type.flexible_options:
        class:     Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleOptionsFilter
        arguments: ["@oro_flexibleentity.registry", "@oro_grid.orm.filter.type.choice"]
        tags:
            - { name: oro_grid.filter.type, alias: oro_grid_orm_flexible_options }
```
