Translatable value
==================

A value can be translated if related attribute is defined as translatable.

By default, attribute is defined as not translatable, you have to setup as following :

```php
$pm = $this->container->get('product_manager');
$attributeCode = 'name';
$attribute = $pm->createAttribute('pim_flexibleentity_text');
$attribute->setCode($attributeCode);
$attribute->setTranslatable(true);
```

You can choose value locale as following and use any locale code you want (fr, fr_FR, other, no checks, depends on application, list of locales is available in Locale Component) :

```php
// add a value (long version ...)
$value = $pm->createFlexibleValue();
$value->setAttribute($attribute);
$value->setData('my data');
$value->setLocale('fr');
$product->addValue($value);

// add a value (shortcut !)
$product->setName('my scoped and translated value', 'fr');

// by using current flexible manager locale
$product->setName('my scoped and translated value');
```

If you don't choose locale of value, it's created with locale code (high to low priority) :
- of flexible entity manager
- of flexible entity config (see default_locale)

Base flexible entity repository is designed to deal with translated values in queries, it knows the asked locale and gets relevant value if attribute is translatable.

Base flexible entity is designed to gets relevant values too, it knows the asked locale (injected with LocalizableListener).

Scopable value
==============

A value can also be scoped if related attribute is defined as scopable.

By default, attribute is defined as not scopable, you have to setup as following :

```php
$pm = $this->container->get('product_manager');
$attributeCode = 'description';
$attribute = $pm->createAttribute('pim_flexibleentity_text');
$attribute->setCode($attributeCode);
$attribute->setTranslatable(true);
$attribute->setScopable(true);
```

Then you can use any scope code you want for value (no checks, depends on application).

```php
$pm = $this->container->get('product_manager');

// add a value (long version ...)
$value = $pm->createFlexibleValue();
$value->setLocale('fr');
$value->setScope('my_scope_code');
$value->setAttribute($attDescription);
$value->setData('my scoped and translated value');
$product->addValue($value);

// add a value (shortcut !)
$product->setDescription('my scoped and translated value', 'fr', 'my_scope_code');

```

If you want associate a default scope to any created value, define it in config file with "default_scope" param.

Base flexible entity repository is designed to deal with scoped values in queries, it knows the asked scope and gets relevant value if attribute is scopable.

Base flexible entity is designed to gets relevant values too, it knows the asked scopable (injected with ScopableListener).

What's a backend type ?
=======================

To allow to type a value, you can use these doctrine mapped fields to store the data : value_string, value_integer, value_decimal, value_text, etc

The used field is defined by the backendType property of the related attribute.

The AbstractEntityFlexibleValue already define some mapping for varchar, integer, decimal, text, date, datetime, options (multi select), option (simple select).

You can also add your own backend type for a custom attribute type.

Add a more complex value
========================

Define
------

Add the doctrine mapping and getter / setter in your value implementation as :

```php
    /**
     * Store price value
     *
     * @var Price $price
     *
     * @ORM\OneToOne(targetEntity="Pim\Bundle\FlexibleEntityBundle\Entity\Price", cascade="persist")
     * @ORM\JoinColumn(name="price_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $price;

    /**
     * Get price
     *
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param Price $price
     *
     * @return AbstractEntityFlexibleValue
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }
```

Use
---

A value can be related to a currency, if related attribute use price backend type.

You can use any currency code you want (no checks, depends on application, list of currencies is available in Locale Component).

```php
$pm = $this->container->get('product_manager');

// create a price
$price = new Price();
$price->setData(5);
$price->setCurrency('USD');

// long version
$value = $pm->createFlexibleValue();
$value->setAttribute($attPrice);
$value->setData($price);
$product->addValue($value);

// shortcut (assuming that we have an existing attribute with a code 'my_price')
$product->setMyPrice($media);
```

