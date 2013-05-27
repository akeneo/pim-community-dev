Translatable value
==================

A value can be translated if related attribute is defined as translatable.

By default, attribute is defined as not translatable, you have to setup as following :

```php
$pm = $this->container->get('product_manager');
$attributeCode = 'name';
$attribute = $pm->createAttribute('oro_flexibleentity_text');
$attribute->setCode($attributeCode);
$attribute->setTranslatable(true);
```

You can choose value locale as following and use any locale code you want (fr, fr_FR, other, no checks, depends on application, list of locales is available in Locale Component) :

```php
$value = $pm->createFlexibleValue();
$value->setAttribute($attribute);
$value->setData('my data');
// force locale to use
$value->setLocale('fr_FR');
```

If you don't choose locale of value, it's created with locale code (high to low priority) :
- of flexible entity manager
- of flexible entity config (see default_locale)

Base flexible entity repository is designed to deal with translated values in queries, it knows the asked locale and gets relevant value if attribute is translatable.

Base flexible entity is designed to gets relevant values too, it knows the asked locale (injected with TranslatableListener).

Scopable value
==============

A value can also be scoped if related attribute is defined as scopable.

By default, attribute is defined as not scopable, you have to setup as following :

```php
$pm = $this->container->get('product_manager');
$attributeCode = 'description';
$attribute = $pm->createAttribute('oro_flexibleentity_text');
$attribute->setCode($attributeCode);
$attribute->setTranslatable(true);
$attribute->setScopable(true);
```

Then you can use any scope code you want for value (no checks, depends on application).

```php
$pm = $this->container->get('product_manager');
$value = $pm->createFlexibleValue();
$value->setScope('my_scope_code');
$value->setAttribute($attDescription);
$value->setData('my scoped and translated value');
```

If you want associate a default scope to any created value, define it in config file with "default_scope" param.

Base flexible entity repository is designed to deal with scoped values in queries, it knows the asked scope and gets relevant value if attribute is scopable.

Base flexible entity is designed to gets relevant values too, it knows the asked scopable (injected with ScopableListener).

What's a backend type ?
=======================

To allow to type a value, you can use these doctrine mapped fields to store the data : value_string, value_integer, value_decimal, value_text, etc

The used field is defined by the backendType property of the related attribute.

The AbstractEntityFlexibleValue already define some mapping for varchar, integer, decimal, text, date, datetime, options (multi select), option (simple select).

There are other backend types defined into flexible bundle as media, metric, price, you can see in the following how to allow their use for your flexible entity.

You can also add your own backend type for a custom attribute type.

Media value
===========

Define
------

Add the doctrine mapping and getter / setter in your value implementation as :

```php
    /**
     * Store upload values
     *
     * @var Media $media
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Media", cascade="persist")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $media;

    /**
     * Get media
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set media
     *
     * @param \Oro\Bundle\FlexibleEntityBundle\Entity\Media $media
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\ProductValue
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }
```

Then, if you want to directly join to these value when do queries on flexible entity, create a custom repository as following :

```php
class ProductRepository extends FlexibleEntityRepository
{
    /**
     * Add join to values tables
     *
     * @param QueryBuilder $qb
     */
    protected function addJoinToValueTables(QueryBuilder $qb)
    {
        parent::addJoinToValueTables($qb);

        $qb->addSelect('ValueMedia');
        $qb->leftJoin('Value.media', 'ValueMedia');
    }
}
```

Define its use in our flexible entity class with the doctrine annotation :

```php
/**
 * @ORM\Table(name="acme_demoflexibleentity_product")
 * @ORM\Entity(repositoryClass="Acme\Bundle\DemoFlexibleEntityBundle\Entity\Repository\ProductRepository")
 */
class Product extends AbstractEntityFlexible
{
}
```

Use
---

```php
$pm = $this->container->get('product_manager');
$value = $pm->createFlexibleValue();
$value->setAttribute($attPrice);
$media = new Media();
$media->setOriginalFilename($uploadedFile->getClientOriginalName());
$media->setFilename($filename);
$media->setFilepath($this->getFilePath($media));
$media->setMimeType($uploadedFile->getMimeType());
$value->setData($media);
$product->addValue($value);
```

Price value
===========

Define
------

Add the doctrine mapping and getter / setter in your value implementation as :

```php
    /**
     * Store price value
     *
     * @var Price $price
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Price", cascade="persist")
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
$value = $pm->createFlexibleValue();
$value->setAttribute($attPrice);
$price = new Price();
$price->setData(5);
$price->setCurrency('USD');
$value->setData($price);
$product->addValue($value);
```

Metric value
============

Define
------

```php

    /**
     * Store metric value
     *
     * @var Metric $metric
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Metric", cascade="persist")
     * @ORM\JoinColumn(name="metric_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $metric;

    /**
     * Get metric
     *
     * @return Metric
     */
    public function getMetric()
    {
        return $this->metric;
    }

    /**
     * Set metric
     *
     * @param Metric $metric
     *
     * @return AbstractEntityFlexibleValue
     */
    public function setMetric($metric)
    {
        $this->metric = $metric;

        return $this;
    }
```

Use
---

A value can be related to a measure unit if related attribute use metric backend type.

You can use any unit code you want (no checks, depends on application).

```php
$value = $pm->createFlexibleValue();
$value->setAttribute($attSize);
$metric = new Metric();
$metric->setUnit('mm');
$metric->setData(10);
$value->setData($metric);
$product->addValue($value);
```
