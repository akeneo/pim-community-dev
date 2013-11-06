Create / use an attribute type
==============================

Create the class
----------------

```php
<?php
namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class TextType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_text';
    }
}
```

Declare as tagged service
-------------------------

With backend type to use and form type to render a value :
```yaml
services:
    pim_flexibleentity.attributetype.text:
        class: Pim\Bundle\FlexibleEntityBundle\AttributeType\TextType
        arguments: ["varchar", "text"]
        tags:
            - { name: pim_flexibleentity.attributetype, alias: pim_flexibleentity_text }
```

Enable it on a flexible entity
------------------------------

```yaml
services:
    product_manager:
        class:     %product_manager_class%
        arguments: [...]
        tags: [...]
        calls:
            - [ addAttributeType, ['pim_flexibleentity_text'] ]
```

Create an attribute of this type
--------------------------------

```php
// from the flexible manager
$manager = $this->container->get('product_manager');
$manager->createAttribute('pim_flexibleentity_text');
// then attribute stored the alias of attribute type
```

Get an attribute type as service
--------------------------------

```php
// from the factory
$factory = $this->container->get('pim_flexibleentity.attributetype.factory');
$factory->get('pim_flexibleentity_text');
```
