Autocomplete Backend
----------------------

#### Overview

Autocomplete consists of next components:

* Form Type Configuration
* Autocomplete Configuration
* Controller
* Search Handler
* Search Handler Factory

#### Form Type Configuration

Field with autocomplete behavior can be added using form type ["oro_jqueryselect2_hidden"](./autocomplete_form_type.md).
Each field must be configured with name of autocomplete configuration.

#### Autocomplete Configuration

Configuration can be stored in **Resources/config/autocomplete.yml** files of any bundle that require some custom
autocomplete fields.

```yml
autocomplete_entities: # Root element
    # Includes only mandatory configs
    simple_users: # unique name of autocomplete configuration
        type: doctrine_entity # type of autocomplete search request handler
        entity_class: FooEntityClassName
        property: username # property that will be displayed and searched by

    # Properties can be defined as list
    users_multiple_properties:
        type: doctrine_entity
        entity_class: FooEntityClassName
        properties: [firstName, lastName]

    # Search using custom query builder
    users_custom_query_builder:
        type: doctrine_query_builder
        options:
            query_builder_service: users_query_builder_service_id
            query_entity_alias: e # optional, entity alias in query
        entity_class: FooEntityClassName
        properties: [firstName, lastName]

    # Search flexible entities
    users_flexible:
        type: flexible
        entity_class: FooEntityClassName
        properties:
            - name: firstName
            - name: lastName

    # Implement custom search handler
    users_custom_service:
        type: service
        options:
            service: users_search_handler_service_id
        entity_class: FooEntityClassName
        properties:
            - name: username

    # All default configs
    users_all_default_configs:
        type: doctrine_entity
        form_options:
            datasource: autocomplete
        options: ~
        entity_class: FooEntityClassName
        properties:
            - name: username
        route: oro_form_autocomplete_search
        view: OroFormBundle:EntityAutocomplete:search.json.twig
```

#### Controller

#### Search Handler

#### Search Handler Factory
