Autocomplete Backend
----------------------

#### Overview

Autocomplete consists of next components:

* Form Type Configuration
* Autocomplete Configuration
* Controller
* Search Handler
* Search Factory

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
            extra_config: autocomplete
        options: []
        entity_class: FooEntityClassName
        properties:
            - name: username
        route: oro_form_autocomplete_search
        acl_resource: ~
        view: OroFormBundle:Autocomplete:search.json.twig
```

#### Controller

Controller and action that handles autocomplete search requests by default is **Controller \ AutocompleteController::searchAction**.
It can be configured via option **route** or **url**.

Autocomplete search request can contain next parameters:
* **name** - name of autocomplete configuration, cannot be empty;
* **query** - search query string;
* **page** - index of results page, must be greater than 0;
* **per_page** - number of result items on page, must be greater than 0;

**Example of Response**

```json
{
    "results": [{
        "id": 1,
        "text": "foo"
    }, {
       "id": 2,
       "text": "bar"
    }, {
        "id": 2,
        "text": "baz"
     }],
    "more": false
}
```

* **results** - contain an array of objects, each object has id and text properties
* **more** - TRUE when other results can be shown on next page, otherwise FALSE

#### Search Handler

Implements **Autocomplete \ SearchHandlerInterface** and used by controller to
handle search requests of autocomplete widgets.

Default search handlers are:

**Autocomplete \ Doctrine \ EntitySearchHandler** (doctrine_entity)

 * requires **entity_class** and **properties** options
 * handles search based on default **Doctrine\ORM\QueryBuilder** created from corresponding Doctrine entity repository.

**Autocomplete \ Doctrine \ QueryBuilderSearchHandler** (doctrine_query_builder)

 * requires **query_builder_service** and **properties** options, which must be a reference to existing service of **Doctrine\ORM\QueryBuilder** type

**Autocomplete \ Flexible \ FlexibleSearchHandler** (flexible)

 * requires **properties** option and either **flexible_manager** or **entity_class** option
 * handles search based on query builder of corresponding flexible entity repository.

**Autocomplete \ SearchIndexer \ IndexerSearchHandler**

 * requires **entity_alias** that represents entity search alias
 * handles search based on search index implemented in OroSearchBundle

You can define your own search handler. To make it supported by default autocomplete controller corresponding search factory must be added.

#### Search Factory

Implements **Autocomplete \ SearchFactoryInterface** and used by controller to
create search handler.

Custom search factory can be added in configuration using tag **oro_form.autocomplete.search_factory**:

```yml
foo_search_factory:
    class: %foo_search_factory.class%
    tags:
        - { name: oro_form.autocomplete.search_factory, alias: foo_type }
```

Right after this definition was added to container, autocomplete configuration can support new type:

```yml
autocomplete_entities:
    foo_name:
        type: foo_type
        entity_class: FooEntityClassName
        property: bar
```

#### TODOS

* Improve **doctrine_query_builder** factory to support services that can be used like factories for QueryBuilder.
* Encapsulate transformation of search results into output format in distinct area apart from controller.
