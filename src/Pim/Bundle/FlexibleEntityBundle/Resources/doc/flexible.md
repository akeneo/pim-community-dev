Create a flexible entity
========================

Flexible entity class
---------------------

We illustrate here the easiest way to create a flexible, ie, by extending abstract classes, you can prefer use flexible and value interfaces.

Here, we create a customer entity class, extends abstract orm entity which contains basic mapping.

This customer class contains fields mapped at development time, here, email, firstname, lastname.

We want give possibility to final user add some custom attributes when he'll use application.

We use the basic entity repository, and define by mapping which value table to use.

```php
namespace Acme\Bundle\DemoFlexibleEntityBundle\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="acmedemo_customer")
 * @ORM\Entity(repositoryClass="Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 */
class Customer extends AbstractEntityFlexible
{
    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    protected $email;

    /**
     * @var string $firstname
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    protected $firstname;

    /**
     * @var string $lastname
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    protected $lastname;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="CustomerValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    // ... getter / setter
```

Flexible value class
--------------------

Then we have to define customer attribute value entity, extends basic one which contains mapping.

We define mapping to basic entity attribute and to our customer entity.
```php
<?php
namespace Acme\Bundle\DemoFlexibleEntityBundle\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Doctrine\ORM\Mapping as ORM;
/**

 * @ORM\Table(name="acmedemo_customer_attribute_value")
 * @ORM\Entity
 */
class CustomerValue extends AbstractEntityFlexibleValue
{
    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\FlexibleEntityBundle\Entity\Attribute")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="values")
     */
    protected $entity;
}
```

We inherit some basic backend types for value, as varchar, text, integer, decimal, datetime, etc, which are in fact fields of a value.

If you want use more complex attributes as list, media, metric related to a unit, a price related to a currency, you need to define mapping to advanced backend entity.

You can also define your own backend type, the attribute backend type is used to know what getter / setter use to bind the value data.

For instance, to use a list of options as backend, add the following mapping in your value class :

```php
    /**
     * Custom backend type to store options and theirs values
     *
     * @var options ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     * @ORM\JoinTable(name="acmedemoflexibleentity_customer_values_options",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;
```

And related getter / setter :
* getOption / setOption : for list with one selectable item
* getOptions / setOptions : for list with many selectable items

You can use Media, Metric and Price as advanced backend.

Configuration
-------------

Then, we configure our flexible entity in src/Acme/Bundle/DemoFlexibleEntityBundle/Resources/config/flexibleentity.yml :
```yaml
entities_config:
    Acme\Bundle\DemoFlexibleEntityBundle\Entity\Customer:
        flexible_manager:     customer_manager
        flexible_class:       Acme\Bundle\DemoBundle\Entity\Customer
        flexible_value_class: Acme\Bundle\DemoBundle\Entity\CustomerValue
        # there is some default values added here for attribute, option, etc, see Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Configuration
```

This config :
- is validated by Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Configuration
- is loaded / merged with others by Pim\Bundle\FlexibleEntityBundle\DependencyInjection\PimFlexibleEntityExtension
- is accessible as $this->container->getParameter('pim_flexibleentity.flexible_config');
- is known by flexible entity manager and repository

Finally we add our service declaration in src/Acme/Bundle/DemoFlexibleEntityBundle/Resources/config/services.yml :
```yaml
parameters:
    customer_manager_class: Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager
    customer_entity_class:  Acme\Bundle\DemoFlexibleEntityBundle\Entity\Customer

services:
    customer_manager:
        class:     "%customer_manager_class%"
        arguments: [%customer_entity_class%, %pim_flexibleentity.flexible_config%, @doctrine.orm.entity_manager, @event_dispatcher, @pim_flexibleentity.attributetype.factory]
        tags:
            - { name: pim_flexibleentity_manager, entity: %customer_entity_class%}
        calls:
            - [ addAttributeType, ['pim_flexibleentity_text'] ]
            - [ addAttributeType, ['pim_flexibleentity_number'] ]
```

Note that tag allows to define the flexible manager and entity on registry.

How to use :
```php
// get customer manager
$cm = $this->container->get('customer_manager');

// create an attribute with one of predefined type
$attCode = 'company';
$att = $cm->createAttribute('pim_flexibleentity_text');
$att->setCode($attCode);

// persist and flush
$cm->getStorageManager()->persist($att);
$cm->getStorageManager()->flush();

// create customer with basic fields mapped in customer entity
$customer = $cm->createFlexible();
$customer->setEmail('name@mail.com');
$customer->setFirstname('Nicolas');
$customer->setLastname('Dupont');

// add a value (long version ...)
$attCompany = $cm->getEntityRepository()->findAttributeByCode('company');
$value = $cm->createFlexibleValue();
$value->setAttribute($attCompany);
$value->setData('Oro');
$customer->addValue($value);

// add a value (shortcut !)
$customer->setCompany('Oro');

// persist and flush
$cm->getStorageManager()->persist($customer);
$cm->getStorageManager()->flush();
```


