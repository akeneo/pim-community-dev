OroFormBundle
=======================

Provide additional form types.

## Table of Contents

- [Form Components](./Resources/doc/reference/form_components.md)

### Autocomplete Form Type

Autocomplete element is based on [GenemuFormBundle](https://github.com/genemu/GenemuFormBundle) [Select2](http://ivaynberg.github.io/select2/)
form type. In case when autocomplete functionality is required for static selects
or for entity based selects generic genemu_jqueryselect2_* form types may be used. For example:

- genemu_jqueryselect2_choice
- genemu_jqueryselect2_country
- genemu_jqueryselect2_entity

oro_jqueryselect2_hidden was created to add more complex support of AJAX based data sources.
Main differences from genemu_jqueryselect2_hidden are:

- support of configuration based autocompletition
- selected value text is shown on entity edit form
- pre-configured ability to work with doctrine entities, flexible entities and grids

#### Default data sources:

*autocomplete*
Backend controller is used to provide search results based on autocompleter comfiguration

*grid*
Selected in configuration grid controller used to provide search results.

Example of autocomplete configuration with *grid* data source

Full configuration

```yaml
autocomplete_entities:
    users:
        type: flexible
        form_options:
            datasource: grid
            grid:
                name: users
                sort_by: sort_field_name
                sort_order: sort_order
        route: oro_user_index
        entity_class: Oro\Bundle\UserBundle\Entity\User
        property: username
```

Minimal required configuration

```yaml
autocomplete_entities:
    users:
        type: flexible
        form_options:
            datasource: grid
            grid:
                name: users
        route: oro_user_index
        entity_class: Oro\Bundle\UserBundle\Entity\User
        property: username
```

Where *route* is route of grid action, *form_options.grid.name* is grid name defined in DatagridManager configuration.

#### Adding custom data source
First of all form_options.datasource options must be set to new data source name. Then create form theme and define oro_combobox_dataconfig_<datasource>
block, in which set select2Config JS object options, which is used to configure Select2 element.
For example you may want to define request parameters for your controller, or transform response data to Select2 format.
