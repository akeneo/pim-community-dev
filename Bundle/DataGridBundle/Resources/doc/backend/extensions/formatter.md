Formatters extension:
=======
This extension do not affects Datasource, it applies after result set is fetched by datagrid and provides changes using formatters that described in config.
Also this extension responsible for passing columns configuration to view layer.

Formatters:
==========

Field
-----
```
column_name:
    type: field
    frontend_type: date|datetime|decimal|integer|percent|options|text|html|boolean # optional default string
```
Represents default data field.

Fixed
-----
```
column_name:
    type: fixed
    value_key: string #required, key in result that shoud represent this field
```
Represent field that contains data from another field

Url
----
```
column_name:
    type: url
    route: some_route # required
    isAbsolute: true|false # optional
    params: [] # optional params for route generating, will be took from record
    anchor: string #optional, use it when need to add some #anchor to generated url
```
Represents url field, mostly used for generating urls for actions.

Twig
-----
```
column_name:
    type: twig
    template: string # required, template name
    context: [] # optional, should not contain reserved keys(record, value)
```
Represents twig template formatted field.

Translatable
-------------
```
column_name:
    type: translatable
    alias: string #optional if need to took value from another column
    domain: string #optional
    locale: string #optional
```
Used when field should be translated by symfony translator.

Callback
-------------
```
column_name:
    type: callback
    callable: @link # required
```
Used when field should be formatted using some callback, format [see](./../../link.md).
