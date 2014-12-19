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
    find ./src -type f -print0 | xargs -0 sed -i '' -e 's/protected function prepareValueFormOptions/public function prepareValueFormOptions/g'
    find ./src -type f -print0 | xargs -0 sed -i '' -e 's/protected function defineCustomAttributeProperties/public function defineCustomAttributeProperties/g'
    find ./src -type f -print0 | xargs -0 sed -i '' -e 's/pim_flexibleentity.validator.attribute_constraint_guesser/pim_catalog.validator.constraint_guesser.chained_attribute/g'
    find ./src -type f -print0 | xargs -0 sed -i '' -e 's/AbstractEntityFlexibleValue implements ProductValueInterface/AbstractProductValue/g'
    find ./src -name '*.ProductManager.php' -print0 | xargs -0 sed -i '' -e 's/save(ProductInterface $product, $recalculate = true, $flush = true)/save(ProductInterface $product, $recalculate = true, $flush = true, $schedule = true)/g'
    find ./src -type f -print0 | xargs -0 sed -i '' -e 's/Entity\\Repository\\ReferableEntityRepository/Doctrine\\ReferableEntityRepository/g'
    find ./src -type f -print0 | xargs -0 sed -i '' 's/Form\\Validator\\ConstraintGuesserInterface/Validator\ConstraintGuesserInterface/g'
    find ./src -type f -name '*.yml' -print0 | xargs -0 sed -i '' 's/pim_base_connector.writer.orm.product/pim_base_connector.writer.doctrine.product/g'
    find ./src -type f -print0 | xargs -0 sed -i '' 's/implements ReferableInterface/extends AbstractCustomOption/g'
    find ./src -type f -print0 | xargs -0 sed -i '' 's/use Pim\\Bundle\\CatalogBundle\\Model\\ReferableInterface/use Pim\\Bundle\\CustomEntityBundle\\Entity\\AbstractCustomOption/g'
    find ./src -type f -print0 | xargs -0 sed -i '' -e 's/flexible_string/product_value_string/g'
    find ./src -type f -print0 | xargs -0 sed -i '' -e 's/flexible_field/product_value_field/g'
    find ./src -type f -print0 | xargs -0 sed -i '' -e 's/pim_flexibleentity.attributetype/pim_catalog.attribute_type/g'
    find ./src -type f -print0 | xargs -0 sed -i '' 's/FlexibleValueInterface/ProductValueInterface/g'
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
```

Some changes have also been done on attribute and product value model :

```
    CREATE UNIQUE INDEX searchunique_idx ON pim_catalog_attribute_option_value (locale_code, option_id);
    ALTER TABLE pim_catalog_attribute DROP backend_storage;
    ALTER TABLE pim_catalog_product_value DROP FOREIGN KEY FK_93A1BBF3EA9FDD75;
    ALTER TABLE pim_catalog_product_value ADD CONSTRAINT FK_93A1BBF3EA9FDD75 FOREIGN KEY (media_id) REFERENCES pim_catalog_product_media (id) ON DELETE CASCADE;
```

## UserBundle

The default user group *All* has been introduced. All users should belong to this user group. You can use the following queries to update your database:

```
INSERT INTO `oro_access_group` (`business_unit_owner_id`, `name`)
VALUES ((SELECT `id` FROM `oro_business_unit` LIMIT 1), 'All');

INSERT INTO `oro_user_access_group` (`user_id`, `group_id`)
SELECT u.`id`, (SELECT g.`id` FROM `oro_access_group` g WHERE g.`name`='All' )
FROM `oro_user` u
```

## VersioningBundle

Version model has been changed to add the MongoDB support.

In case of standard ORM use, to update and keep your existing history, you can use following queries,

```
    ALTER TABLE pim_versioning_version DROP FOREIGN KEY FK_A99EF708A76ED395;
    DROP INDEX IDX_A99EF708A76ED395 ON pim_versioning_version;
    ALTER TABLE pim_versioning_version ADD author VARCHAR(255) NOT NULL, ADD snapshot LONGTEXT DEFAULT NULL COMMENT '', ADD pending TINYINT(1) NOT NULL, CHANGE changeset changeset LONGTEXT NOT NULL COMMENT '', CHANGE version version INT DEFAULT NULL;
    UPDATE pim_versioning_version version LEFT JOIN oro_user user ON version.user_id = user.id SET version.author = user.username;
    UPDATE pim_versioning_version version SET version.snapshot = version.data;
    ALTER TABLE pim_versioning_version DROP user_id, DROP data;
    CREATE INDEX pending_idx ON pim_versioning_version (pending);
```

### Version migration - ORM storage only

A bug with versioning empty prices on ORM has been fixed, launch the following script to update existing product versions:

**Make sure you backup your database before launching this script**

```
    $ php upgrades/1.1-1.2/orm/migrate_versions.php --env=<environment>
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

Change of ConfiguratorInterface and configurators are now services to make them easier to customize.

A constraint has been added on the dategrid view model :

```
    ALTER TABLE pim_datagrid_view CHANGE `label` `label` VARCHAR(100) NOT NULL;
```

## OroSegmentationTreeBundle

The bundle has been removed from Oro Platform, entities extending AbstractSegment should implement the desired
methods themselves and repositories extending SegmentRepository should extend Gedmo\Tree\Entity\Repository\NestedTreeRepository

## FlexibleEntityBundle

As announced during last release in UPGRADE-1.1.md, the bundle has been removed.
