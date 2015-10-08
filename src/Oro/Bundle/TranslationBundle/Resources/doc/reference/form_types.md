Form Types
----------

Translation bundle provide form types for easier translation on frontend.


### Form Types Description

#### translatable\_entity

This form type works exactly as regular [entity form type](http://symfony.com/doc/current/reference/forms/types/entity.html),
but it supports translatable entities and performs translation using one DB request.

Options:

* **class** - entity class name, this option is required;
* **property** - class property that should be used as label, by default string representation of entity will be used;
* **query\_builder** - custom query builder or callback to extract entities.

### Classes Description

* **TranslationBundle \ Form \ Type \ TranslatableEntityType** - class for translatable\_entity form type,
provides functionality to work with translatable entities;
* **TranslationBundle \ Form \ DataTransformer \ CollectionToArrayTransformer** - extends standard Doctrine transformer
to support empty array as data source.


### Configuration

```
parameters:
    oro_form.type.translatable_entity.class:  Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType

services:
    oro_form.type.translatable_entity:
        class: %oro_form.type.translatable_entity.class%
        arguments: ["@doctrine"]
        tags:
            - { name: form.type, alias: translatable_entity }
```
