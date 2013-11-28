Search Bundle
========================

Search bundle create search index for mapping objects and allow to create advanced queries to this indexed data.

Install project
----------------------------------

**Update database structure**

MySql, Postgres and other db engines use additional indexes for fulltext search. To create this indexes use console command

```
php app/console oro:search:create-index
```

MySql config
----------------------------------

By default, search engine use MySQL fulltext search index to make search in text data. in MySQL config where is  ft_min_word_len
parameter - the minimum length of the word to be included in a fulltext index. Default value of this parameter is 4
http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_ft_min_word_len .

It's recommended to change this value to 3 http://dev.mysql.com/doc/refman/5.1/en/fulltext-fine-tuning.html .


Bundle config
----------------------------------

Main bundle config store in config.yml file in oro_search section.

oro_search parameter supports next parameter strings:

- **engine** - engine to use for indexing. Now supports only orm engine
- **entities_config** - array with mapping entities config.
- **item_container_template** - the default template for search result items (by default it is OroSearchBundle:Datagrid:itemContainer.html.twig)

Mapping config
----------------------------------

After insert, update or delete entity records, search index must be updated. Search index consist of data from entities by mapping parameters.

In entity mapping config we map entity fields to virtual search fields in search index.

Entity mapping configuration can be store in main config.yml file (in search bundle config section) or in search.yml files in config directory of the bundle.

Configuration is array that contain info about bundle name, entity name and array of fields.

Fields array contain array of field name and field type.

All text fields data will be store in **all_text** virtual field. Additionally, all the fields will be stored in fieldName virtual fields, if not set target_fields parameter.

Example:

```
    Acme\DemoBundle\Entity\Product:
        alias: demo_product
        label: Demo products
        route:
            name: acme_demo_search_product
            parameters:
                id: id
        title_fields: [name]
        fields:
            -
                name: name
                target_type: text
            -
                name: description
                target_type: text
                target_fields: [description, another_index_name]
            -
                name: manufacturer
                relation_type: many-to-one
                relation_fields:
                    -
                        name: name
                        target_type: text
                        target_fields: [manufacturer, all_data]
                    -
                        name: id
                        target_type: integer
                        target_fields: [manufacturer]
            -
                name: categories
                relation_type: many-to-many
                relation_fields:
                    -
                        name: name
                        target_type: text
                        target_fields: [all_data]
```

Parameters:

- **search_template** - template to use in search result page for this entity type
- **label**: Label for entity to identify entity in search results
- **route**: **name** - route name to generate url link tho the entity record, **parameters** - array with parameters for route
- **alias**: alias for 'from' keyword in advanced search
- **name**: name of field in entity
- **target_type**: type of virtual search field. Supported target types: text (string and text fields), integer, double, datetime
- **target_fields**: array of virtual fields for entity field from 'name' parameter.
- **relation_type**: indicate that this field is relation field to another table. Supported relation types: one-to-one, many-to-many, one-to-many, many-to-one.
- **relation_fields**: array of fields from relation record we must to index.

[Query builder](Resources/doc/query_builder.md)

[API simple search](Resources/doc/simple_search.md)

[API advanced search](Resources/doc/advanced_search.md)


Run unit tests
----------------------------------

To run tests for bundle, use command

```
phpunit -c app src/Oro/Bundle/SearchBundle/
```
