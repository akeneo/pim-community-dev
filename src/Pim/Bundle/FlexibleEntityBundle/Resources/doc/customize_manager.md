Use a non default entity manager
================================

Used entity manager is the default one "doctrine.orm.entity_manager"

If you want to use another one, you can define it with optional parameter as following :

```yaml
services:
    customer_manager:
        class:     "%customer_manager_class%"
        arguments: [%customer_entity_class%, %pim_flexibleentity.flexible_config%, @doctrine.orm.non_default_entity_manager, @event_dispatcher]
```

Extend flexible manager
=======================

Begin by extend FlexibleManager in your bundle :

```php
<?php
class CustomerManager extends FlexibleManager
```

Configure the use of your custom manager in service.yml :
```yaml
parameters:
    customer_manager_class: Acme\Bundle\DemoBundle\Manager\CustomerManager
    customer_entity_class:  Acme\Bundle\DemoBundle\Entity\Customer

services:
    customer_manager:
        class:     "%customer_manager_class%"
        arguments: [%customer_entity_class%, %pim_flexibleentity.flexible_config%, @doctrine.orm.non_default_entity_manager, @event_dispatcher]
```

Extend flexible repository
==========================

Begin by extend FlexibleEntityRepository in your bundle as :

```php
<?php
class ProductRepository extends FlexibleEntityRepository
```

Configure the use of custom repository in your flexible entity class as :

```php
<?php
/**
 * Flexible product
 * @ORM\Table(name="acmeproduct_product")
 * @ORM\Entity(repositoryClass="Acme\Bundle\DemoBundle\Entity\Repository\ProductRepository")
 */
class Product extends AbstractEntityFlexible
{
```
