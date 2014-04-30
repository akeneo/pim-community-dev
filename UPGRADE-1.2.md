UPGRADE FROM 1.1 to 1.2
=======================

General
-------

FlexibleEntityBundle
--------------------

As announced in 1.1 upgrade, this bundle has been removed.


CatalogBundle
-------------

In ./src/Pim/Bundle/CatalogBundle/Resources/config/managers.yml:

With 1.1 :
```
    flexible_class:               %pim_catalog.entity.product.class%
    flexible_value_class:         %pim_catalog.entity.product_value.class%
```

With 1.2 :
```
    product_class:                %pim_catalog.entity.product.class%
    product_value_class:          %pim_catalog.entity.product_value.class%
```

./src/Pim/Bundle/CatalogBundle/Manager/ProductManager.php has been updated to use these new configuration parameters


MongoDB implementation
----------------------

We removed null values from normalizedData field to avoid storing useless values
