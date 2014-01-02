Add some behavior related to flexible
=====================================

- use event / subscriber to plug custom code, notice that bundle define some custom events on global dispatcher
- if needed, you can retrieve relevant flexible manager from entity full qualified class name as :

```php
<?php
// get manager from registry
$registry = $this->container->get('pim_flexibleentity.registry');
$registry->getManagers();
$registry->getEntityToManager();
$registry->getManager($flexibleEntityClass);

// get manager from flexible config
$flexibleConfig = $this->container->getParameter('pim_flexibleentity.flexible_config');
$flexibleManagerName = $flexibleConfig['entities_config'][$flexibleEntityClass]['flexible_manager'];
$flexibleManager = $this->container->get($flexibleManagerName);
```

Store attributes, option, option values in custom tables
========================================================

- extend or replace Attribute, AttributeOption, AttributeOptionValue in your bundle
- define the classes to use in our flexibleentity.yml with properties : 'attribute_class', 'attribute_option_class', 'attribute_option_value_class'

Use ORM flat storage for values
===============================

- use another backend storage for attribute, as flatValues (can define this relation in your flexible entity)
- extends / replace flexible repository to change queries
- use event / subscriber to change schema on each attribute insert / update / delete

Use ODM storage for flexible/values
===================================

- define your document class and flexible manager in your bundle
- define flexible document manager and inject it in your flexible service manager as :

```yaml
parameters:
    mydoc_manager_class: Acme\Bundle\MyBundle\Manager\MyFlexibleManager
    mydoc_entity_class:  Acme\Bundle\MyBundle\Document\MyDocument

services:
    customer_manager:
        class:     "%mydoc_manager_class%"
        arguments: [@service_container, %mydoc_entity_class%]
```
