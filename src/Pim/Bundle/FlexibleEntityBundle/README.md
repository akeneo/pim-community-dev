Flexible entity
===============

Areas of responsibilities :
- create a flexible entity with dynamic attribute management
- create flexible and attribute forms by using basic form type
- extend / customize your flexible entity for business needs

Based on classic Doctrine 2 classes, entity, repository, entity manager

Install
=======

To install for dev :

```bash
$ php composer.phar update --dev
```
To use as dependency, use composer and add bundle in your AppKernel :

```yaml
    "require": {
        [...]
        "akeneo/FlexibleEntityBundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:akeneo/FlexibleEntityBundle.git",
            "branch": "master"
        }
    ]

```

Run unit tests
==============

```bash
$ phpunit --coverage-html=cov/
```

Main classes / concepts
=======================

- Attribute : the smallest entity a property (as a name, a sku, a price, a color) which have a type and some configuration
- AttributeType : aims to configure basic attribute configuration as storage, rendering
- Flexible : the flexible entity, ie, an entity which support usage of dynamic attributes
- FlexibleValue : the value related to an entity and an attribute
- FlexibleManager : the service which allows to easily manipulate flexible entity and provides "glue" between pieces
- FlexibleRepository : aims to build query on flexible storage

How to use ?
============

- [Create a flexible entity](Resources/doc/flexible.md)
- [Create an attribute type](Resources/doc/attribute_type.md)
- [Use flexible repository](Resources/doc/repository.md)
- [Dive into flexible value](Resources/doc/value.md)
- [Create a flexible form](Resources/doc/flexible_form.md)
- [Customize manager and repository](Resources/doc/customize_manager.md)
- [Others customizations](Resources/doc/customize_others.md)

Enhancement
===========

- customize doctrine query cache for common queries as retrieve available attributes for a flexible entity
- add a default is_unique behavior
- default fallback (locale, scope) in queries
- add a "multivalued" parameter to any attribute in order to have multiple values for the same attribute (manufacturer atttibute: several manufacturers, gallery attribute: several images, documentation attribute: several files)
