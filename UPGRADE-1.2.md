UPGRADE FROM 1.1 to 1.2
=======================

General
-------

BatchBundle
-----------

Please run the following commands against your database :

    ALTER TABLE akeneo_batch_job_execution ADD pid INT DEFAULT NULL;
    CREATE TABLE akeneo_batch_warning (id INT AUTO_INCREMENT NOT NULL, step_execution_id INT DEFAULT NULL, name VARCHAR(100) DEFAULT NULL, reason LONGTEXT DEFAULT NULL, reason_parameters LONGTEXT NOT NULL COMMENT '(DC2Type:array)', item LONGTEXT NOT NULL COMMENT '(DC2Type:array)', INDEX IDX_8EE0AE736C7DA296 (step_execution_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    ALTER TABLE akeneo_batch_warning ADD CONSTRAINT FK_8EE0AE736C7DA296 FOREIGN KEY (step_execution_id) REFERENCES akeneo_batch_step_execution (id) ON DELETE CASCADE;
    ALTER TABLE akeneo_batch_step_execution DROP warnings;


FlexibleEntityBundle
--------------------

As announced during last release in UPGRADE-1.1.md, the bundle has been removed.

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
The normalized media now contains originalFilename

DataGridBundle
--------------

Change of ConfiguratorInterface and configurators are now services to make them easier to customize

OroSegmentationTreeBundle
--------------

The bundle has been removed from Oro Platform, entities extending AbstractSegment should implement the desired
methods themselves and repositories extending SegmentRepository should extend Gedmo\Tree\Entity\Repository\NestedTreeRepository

EnrichBundle
--------------
The virtual attribute group `Other` has been deleted. The attribute group is now a mandatory parameter for creating of editing
an attribute group. This means you now have to set an attribute group for all the orphans attributes. This can be done
thanks to the following queries :

```
    INSERT INTO `pim_catalog_attribute_group` (`code`, `sort_order`, `created`, `updated`)
    VALUES ('other', 100, NOW(), NOW());

    UPDATE `pim_catalog_attribute` a
    SET a.`group_id` = (SELECT g.`id` FROM `pim_catalog_attribute_group` g WHERE g.`code`='other')
    WHERE a.`group_id` IS NULL
```
