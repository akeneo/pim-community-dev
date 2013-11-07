Datagrid
========

Table of content
-----------------
- [Overview](#overview)
- [Getting Started](#getting-started)
- [Advanced grid configuration](./advanced_grid_configuration.md)
- [Implementation](#implementation)
- [Extendability](#extendability)

##Overview

Datagrid is table oriented representation of some data from some datasource.
 It's configuration is declarative YAML based file, that should be placed in `Resources/config` folder of your bundle and named `datagrid.yml`.
  This file should contain root node `datagrid` and each grid configuration should be placed under it.

##Getting Started
####Configuration file
First of all to define own datagrid you should create configuration file as described in "overview" section.
After that, you have to choose identifier of yours future grid and declare it by adding associative array with identifier as key.
e.g.
``` yaml
datagrid:
    acme-demo-datagrid:     # grid identifier
        ...                 # configuration will be here
``` 

####Datasource
When it's done, next step is to configure datasource, basically it's similar array under `source` node.
You have to choose datasource type and properly configure  depending on it. For further details [see](./datasources.md).
e.g.
``` yaml
datagrid:
    acme-demo-datagrid:
        source:
            type: orm  # datasource type
            query:
                ....   # some query configuration
``` 

####Columns and properties
Next step is columns definition. It's array as well as other parts of grid configuration.
 Root node for columns is `columns`, definition key should be unique column identifier, value is array of column configuration.
  The same for properties, but root node is `properties`.

Property is similar something similar to column, but it has not frontend representation.
Usually they are used when needs to pass some additional data that should be generated for each row(e.g urls for actions, row identifier for some needs etc).

**Note:** _column identifier is used for some suggestion, so best practice is to use identifier similar with data identifier (e.g field name in DQL)_

**Note:** _Usually row identifier property should be added for correct work, but for simplest grids it's excess_

Configuration format is different depending on column type, but there are list of keys shared between all types.

- `type`  - backend formatter type (`field` - by default)
- `label` - column title (translated on backend, translation should be placed in "messages" domain)
- `frontend_type` - frontend formatters that will process the column value (`string` - by default)
- `editable` - is column editable on frontend (`false` - by default)
- `renderable` - should column be rendered (`true` - by default)
- `data_name` - data identifier (column name suggested by default)

For detailed explanation [see](./extensions/formatter.md).

So lets define few columns:
``` yaml
datagrid:
    acme-demo-datagrid:
        source:
            type: orm
            query:
                select: [ o.firstName, o.lastName, o.age ]
                from: 
                    - { table: AcmeDemoBundle:Entity, alias: o }
        columns:
            firstName:                                   # data identifier will be taken from column name
                label: acme.demo.grid.columns.firstName  # translation string
            lastName:
                label: acme.demo.grid.columns.firstName  # translation string
            age:
                label: acme.demo.grid.columns.age        # translation string
                frontend_type: number                    # needed for correct l10n (e.g. touthand, decimal separators etc)
``` 

####Sorting
After that you may want to make your columns sortable. Sorting configuration should be placed under `sorters` node.
 In basic sorter implementation, configuration takes `columns` and `default` keys.
Basically it's array of column names where value is sorter configuration.
 There is one required value `data_name` that responsible of knowledge on which data grid should do sorting.

Lets make all columns sortable:
``` yaml
datagrid:
    acme-demo-datagrid:
        ...                                 # definition from previos examples
        sorters:
            columns:
                firstName:
                    data_name: o.firstName
                lastName:
                    data_name: o.lastName
                age:
                    data_name: o.age
            default:
                lastName: DESC              # Default sorting, allowed values ASC|DESC
``` 

For detailed explanation [see](./extensions/sorter.md).

####Final step
Final step for this tutorial is to add grid to template.
There is predefined macros used for grid render. It defined in ` OroDataGridBundle::macros.html.twig` and could be imported
by following call `{% import 'OroDataGridBundle::macros.html.twig' as dataGrid %}` .
Macros name is `renderGrid`, it takes 2 arguments: grid name, route parameters(used for advanced query building).
So for displaying our grid we have to add following code to templete:

``` twig
{% import 'OroDataGridBundle::macros.html.twig' as dataGrid %}
{% block content %}
     {{ dataGrid.renderGrid('acme-demo-datagrid') }}
{% endblock %}
```
**Note:** _if your template extends OroUIBundle:actions:index.html.twig template macros will be already imported and just set variable `gridName` make grid renered_

Actions, mass actions, toolbar, pagers, grid views and other functionality are explained on [advanced grid configuration](./advanced_grid_configuration.md) page.

##Implementation
There is [diagram](./diagrams/datagrid_base_uml.jpg) of base classes, that should explain basic idea of current implementation.

##Extendability
