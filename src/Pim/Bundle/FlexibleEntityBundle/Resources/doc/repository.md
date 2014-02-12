About queries on flexible entity
================================

Repository
----------

Flexible entity uses a flexible repository which already contains useful method to deal with main data querying.

We can use classic findBy() method of repository to retrieve entity collection (native Symfony shortcurt to build doctrine query)

```php
// get only entities, values and attributes are lazy loaded, you can use any criteria, order, limit
$products = $this->container->get('product_manager')->getFlexibleRepository()->findBy(array());

```
We have added a findAllByAttributes() in flexible repository which have the same signature, just attribute codes to select as first param.

This method cover the same features than findBy, add basic criterias, order by, limit on field or attribute.

```php
$productManager = $this->container->get('product_manager');
$productRepository = $productManager->getFlexibleRepository();
// get all entity fields and values (no lazy loading)
$products = $productRepository->findAllByAttributes();
// select few attributes
$products = $productRepository->findAllByAttributes(array('name'));
// filter on field and attribute values
$products = $productRepository->findAllByAttributes(array(), array('sku' => 'sku-2'));
$products = $productRepository->findAllByAttributes(array('description', 'size'), array('size' => 175));
// use order
$products = $productRepository->findAllByAttributes(
    array('name', 'description'), null, array('description' => 'desc', 'id' => 'asc')
);
// use limit
$products = $productRepository->findAllByAttributes(array('name', 'description'), null, null, 10, 0);
// force locale to get french values
$productManager->setLocale('fr')->getFlexibleRepository()->findAllByAttributes(array('name', 'description'));
```

There is also a method to load a flexible entity and all values without lazy loading :

```php
// to load one flexible entity with lazy loading, classic way
$customer = $this->container->get('customer_manager')->getFlexibleRepository()->find($id);

// with all values not lazy loaded with new method
$customer = $this->container->get('customer_manager')->getFlexibleRepository()->findWithAttributes($id);
```

You can easily extends the flexible repository and define the use in your flexible entity to add some custom business methods.

Query Builder
-------------

You can use the method createFlexibleQueryBuilder to return the FlexibleQueryBuilder which already embed some logic to deal with flexible storage and querying (operator, locale, scope).

As it returns a QueryBuilder you can get the query add some very custom clauses, add lock mode, change hydration mode, etc.

