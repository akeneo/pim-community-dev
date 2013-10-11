Fields:
=======

Field
-----
```
column_name:
    type: field
    frontend_type: date|datetime|decimal|integer|percent|options|text|html|boolean # optional default string
```
Represents default data field.

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
