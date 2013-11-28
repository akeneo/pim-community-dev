Configuration
-------------

Configuration files must contains services configuration for Datagrid Managers and all theirs custom dependencies.
Datagrid Manager dependencies should be passed using either tag attributes, or manually using
[setter method injection](http://symfony.com/doc/master/book/service_container.html#optional-dependencies-setter-injection).

#### Datagrid Manager Configuration

Datagrid Manager receives parameters through tag attributes. List of parameters and attributes is presented below.

* **class** - Datagrid Manager class name;
* **name** - reserved Datagrid Manager tag name;
* **datagrid\_name** - datagrid unique ID, used to set datagrid name and isolate separate grids from each other; setter method is *setName*;
* **entity\_hint** (optional) - string which is used to set UI datagrid name; setter method is *setEntityHint*;
* **entity\_name** (optional) - string that represents Doctrine entity name which should be used to select;
* **query\_entity\_alias** (optional) - string that represents Doctrine entity alias which should be used in request;
* **route\_name** - used to create default Route Generator based on specified route name; can be optional if user specified route_generator parameter;
* **query\_factory** (optional) - Query Factory service ID which will be passed to Datagrid Manager; setter method is *setQueryFactory*;
* **route\_generator** (optional) - Route Generator service ID which will be passed to Datagrid Manager; setter method is *setRouteGenerator*;
* **parameters** (optional) - Parameters service ID which will be passed to Datagrid Manager; setter method is *setParameters*;
* **datagrid\_builder** (optional) - Datagrid Builder service ID which will be passed to Datagrid Manager; setter method is *setDatagridBuilder*;
* **list\_builder** (optional) - List Builder service ID which will be passed to Datagrid Manager; setter method is *setListBuilder*;
* **translator** (optional) - Symfony Translator service ID which will be passed to Datagrid Manager; setter method is *setTranslator*;
* **validator** (optional) - Symfony Validator service ID which will be passed to Datagrid Manager; setter method is *setValidator*.

```
services:
    acme_demo_grid.product_grid.manager:
        class: Acme\Bundle\DemoGridBundle\Datagrid\ProductDatagridManager
        tags:
            - name: oro_grid.datagrid.manager
              datagrid_name: product
              entity_hint: products
              entity_name: ~
              query_entity_alias: ~
              route_name: acme_demo_gridbundle_product_list
              query_factory: ~
              route_generator: ~
              parameters: ~
              datagrid_builder: ~
              list_builder: ~
              translator: ~
              validator: ~
```

