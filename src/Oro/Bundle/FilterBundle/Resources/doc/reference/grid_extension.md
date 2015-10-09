Grid Extension
==============

Overview
--------

Filter bundle provides extension for data grid with ORM datasource.
Filters could be added to datagrid in the datagrid.yml file for specified datagrid under `filters` node.
Definition of any filter has required option `data_name` that should be reference to column in query and type - filter type.
For example:

```
    SOME_DATAGRID:
        source:
            type: orm
            query:
                select:
                    - g.id
                    - g.label
                from:
                    - { table: OroCRMContactBundle:Group, alias: g }

        filters:
            SOME_FITLER_NAME: # uses for query param, and for setting default filters
                type: string  # Filter type, list of available types described below
                data_name: g.id

```

## Additional params

 - `filter_condition` - use OR or AND operator in expression
 - `filter_by_having` - filter expression should be added to HAVING clause
 - `options` - pass form options directly to filter form type (for additional info [see](./filter_form_types.md)

Filters
-------

### String filter

Provides filtering using string comparison.

`type: string`
Validated by TextFilterType on backend and rendered by [Oro.Filter.ChoiceFilter](./javascript_widgets.md#orofilterchoicefilter)

### Select Row filter

Provides filtering by selected/not selected records

`type: string`
Validated by [SelectRowFilterType](./filter_form_types.md#oro_type_selectrow) on backend.

### Number and percent filter

Provides filtering by numbers comparison.

**Note**: _value from frontend will automatically transform to percentage for "percent" filter_

`type: number` - integer/decimal filter

`type: percent` - percent filter

Validated by [NumberFilterType](./filter_form_types.md#oro_type_number_filter-form-type) on backend
and rendered by [Oro.Filter.NumberFilter](./javascript_widgets.md#orofilternumberfilter)

### Boolean filter

Provides filtering for boolean values.

`type: boolean`

Validated by [BooleanFilterType](./filter_form_types.md#oro_type_boolean_filter-form-type) on backend
and rendered by [Oro.Filter.ChoiceFilter](./javascript_widgets.md#orofilterchoicefilter) with predefined set of option (yes/no)

**Additional params:**

`nullable: true|false` - is boolean column nullable or no

### Choice filter

Provides filtering data using list of predefined choices

`type: choice`

Validated by [ChoiceFilterType](./filter_form_types.md#oro_type_choice_filter-form-type) on backend
and rendered by [Oro.Filter.ChoiceFilter](./javascript_widgets.md#orofilterchoicefilter)

### Entity filter

Provides filtering data using list of choices that extracted from database.

`type: entity`

Validated by [EntityFilterType](./filter_form_types.md#oro_type_entity_filter-form-type) on backend
and rendered by [Oro.Filter.ChoiceFilter](./javascript_widgets.md#orofilterchoicefilter)

**Note**: _`query_builder` option could be passed from yml configuration to `field_options` using [method call link](./../../link.md)._

### Date filter

Provides filtering data by date values

`type: date`

Validated by [DateRangeFilterType](./filter_form_types.md#oro_type_date_range_filter-form-type)
Rendered by [Oro.Filter.DateFilter](./javascript_widgets.md#orofilterdatefilter)

### DateTime filter

Provides filtering data by datetime values

`type: datetime`

Validated by [DateTimeRangeFilterType](./filter_form_types.md#oro_type_datetime_range_filter-form-type)
Rendered by [Oro.Filter.DateTimeFilter](./javascript_widgets.md#orofilterdatetimefilter)

Customization
-------------
To implement your filter you have to do following:

 - Develop class that implements Oro\Bundle\FilterBundle\Filter\FilterInterface (also there is basic implementation in AbstractFilter class)
 - Register you filter as service with tag { name: oro\_filter.extension.orm\_filter.filter, type: YOUR\_FILTER\_TYPE }
