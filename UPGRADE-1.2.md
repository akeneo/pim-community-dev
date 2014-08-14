# UPGRADE FROM 1.1 to 1.2

## General

### Fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

*It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance*

Based on a pim standard installation, execute the following command in your project folder :

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim.attribute_constraint_guesser/pim_catalog.constraint_guesser.attribute/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.validator.attribute_constraint_guesser/pim_catalog.validator.constraint_guesser.chained_attribute/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Model\\Media/Model\\ProductMedia/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog_media/pim_catalog_product_media/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/EnrichBundle\\MassEditAction/EnrichBundle\\MassEditAction\\Operation/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/flexible_class/product_class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/flexible_value_class/product_value_class/g'
    find ./src/ -type f -name '*.yml'  -print0 | xargs -0 sed -i 's/parent_type/ftype/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/VersioningBundle\\EventListener/VersioningBundle\\EventSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/VersioningBundle\\EventSubscriber\\AddContextListener/VersioningBundle\\EventSubscriber\\AddContextSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/VersioningBundle\\EventSubscriber\\AddUserListener/VersioningBundle\\EventSubscriber\\AddUserSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/VersioningBundle\\EventSubscriber\\AddVersionListener/VersioningBundle\\EventSubscriber\\AddVersionSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/VersioningBundle\\EventSubscriber\\MongoDBODM\\AddVersionListener/VersioningBundle\\EventSubscriber\\MongoDBODM\\AddProductVersionSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_versioning.event_listener/pim_versioning.event_subscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/UserBundle\\EventListener/UserBundle\\EventSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_user.event_listener/pim_user.event_subscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\EventListener/CatalogBundle\\EventSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.event_listener/pim_catalog.event_subscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/OutdateIndexedValuesListener/OutdateIndexedValuesSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/ScopableListener/ScopableSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/TimestampableListener/TimestampableSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/InitializeValuesListener/InitializeValuesSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/LocalizableListener/LocalizableSubscriber/g'
```

## Translations

Has been moved from app/Resources to bundles

## Configuration

The app/config/config.yml and parameters.yml have been changed

## BatchBundle

Please run the following commands against your database :

    ALTER TABLE akeneo_batch_job_execution ADD pid INT DEFAULT NULL;
    CREATE TABLE akeneo_batch_warning (id INT AUTO_INCREMENT NOT NULL, step_execution_id INT DEFAULT NULL, name VARCHAR(100) DEFAULT NULL, reason LONGTEXT DEFAULT NULL, reason_parameters LONGTEXT NOT NULL COMMENT '(DC2Type:array)', item LONGTEXT NOT NULL COMMENT '(DC2Type:array)', INDEX IDX_8EE0AE736C7DA296 (step_execution_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
    ALTER TABLE akeneo_batch_warning ADD CONSTRAINT FK_8EE0AE736C7DA296 FOREIGN KEY (step_execution_id) REFERENCES akeneo_batch_step_execution (id) ON DELETE CASCADE;
    ALTER TABLE akeneo_batch_step_execution DROP warnings;

## CatalogBundle

The Media model has been renamed to ProductMedia, please run the following commands against your database :

    RENAME TABLE pim_catalog_media TO pim_catalog_product_media;

The virtual attribute group `Other` has been deleted. The attribute group is now a mandatory parameter for creating of editing an attribute group. This means you now have to set an attribute group for all the orphans attributes.

This can be done thanks to the following queries :

```
    INSERT INTO `pim_catalog_attribute_group` (`code`, `sort_order`, `created`, `updated`)
    VALUES ('other', 100, NOW(), NOW());

    UPDATE `pim_catalog_attribute` a
    SET a.`group_id` = (SELECT g.`id` FROM `pim_catalog_attribute_group` g WHERE g.`code`='other')
    WHERE a.`group_id` IS NULL;
    
    ALTER TABLE `pim_catalog_attribute`
    DROP `backend_storage`;
```

## MongoDB implementation

### Normalized data updates

We removed null values from normalizedData field to avoid storing useless values.
The normalized media now contains originalFilename.

### Media migration

The media object is now stored as an embedded document inside the product instead
of being in its own collection. This avoids unnecessary queries and makes media
behave like other objects link to value (metric and price).

To migrate existing media data from 1.1 to 1.2, launch the following script:

**Make sure to backup your database (with mongodump for example) before launching this script**

```
    $ php upgrades/1.1-1.2/mongodb/migrate_images.php <mongodb_server> <mongodb_database>
```

*Please note that if you use different collections for product and media,
please change them at the beginning of the script to accomodate your environment.*

Once this script has been executed without errors, you can remove the now useless
media collection with the mongo client:
```
    $ mongo akeneo_pim
    MongoDB shell version: 2.4.10
    connecting to: akeneo_pim
    > db.pim_catalog_media.drop();
    true

```

## DataGridBundle

Change of ConfiguratorInterface and configurators are now services to make them easier to customize

## OroSegmentationTreeBundle

The bundle has been removed from Oro Platform, entities extending AbstractSegment should implement the desired
methods themselves and repositories extending SegmentRepository should extend Gedmo\Tree\Entity\Repository\NestedTreeRepository

## FlexibleEntityBundle

As announced during last release in UPGRADE-1.1.md, the bundle has been removed.
