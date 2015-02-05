# UPGRADE FROM 1.2 to 1.3

## Akeneo storage

All classes related to Akeneo dual storage (ORM and/or MongoDB) have been moved in a dedicated bundle called *AkeneoStorageUtilsBundle*.

Normally you should not much be impacted by this internal change. The main change concerns the parameter `pim_catalog_storage_driver` that has been renamed. 
**In case your products are stored in Mongo**,  your have to replace the parameter `pim_catalog_storage_driver` by `pim_catalog_product_storage_driver` in your `app/config/parameters.yml` configuration file.

A lot of internal classes and services have been moved and/or renamed. To get the list of all impacts related to this change, please read the lines of the [CHANGELOG](https://github.com/akeneo/pim-community-standard/blob/master/CHANGELOG.md) that are prefixed by *(Akeneo storage)*.
Please note that former services are still supported thanks to aliases until version 1.4.

## Update dependencies and configuration

Download the latest [PIM community standard](http://www.akeneo.com/pim-community-standard.tar.gz) and extract it:

```
 wget http://www.akeneo.com/pim-community-standard.tar.gz
 tar -zxf pim-community-standard.tar.gz
 cd pim-community-standard/
```

Copy the following files to your PIM installation:

``` 
 export PIM_DIR=/path/to/your/pim/installation
 cp app/PimRequirements.php $PIM_DIR/app
 cp app/SymfonyRequirements.php $PIM_DIR/app
 cp app/config/pim_parameters.yml $PIM_DIR/app/config
 cp composer.json $PIM_DIR
```

**In case your products are stored in Mongo**, don't forget to re-add the mongo dependencies to your *composer.json*:

```
 "doctrine/mongodb-odm": "v1.0.0-beta10@dev",
 "doctrine/mongodb-odm-bundle": "v3.0.0-BETA6@dev"
```

Merge the following files into your PIM installation:
 - *app/AppKernel.php*: we have registered some new bundles and reorganized this file to make more clear. The easiest way to merge is to copy the PIM-1.3 AppKernel file into your installation, and then register your custom bundles.
 - *app/config/routing.yml*: we have added the entries *pim_dashboard*, *pim_comment*, *pim_pdf_generator* and *pim_notification* 

Now you're ready to update your dependencies:

```
 cd $PIM_DIR
 composer update
```

## Partially fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

**It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance.**

Based on a pim standard installation, execute the following command in your project folder:

```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\AttributeFilterInterface/CatalogBundle\\Doctrine\\Query\\AttributeFilterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\FieldFilterInterface/CatalogBundle\\Doctrine\\Query\\FieldFilterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\AttributeSorterInterface/CatalogBundle\\Doctrine\\Query\\AttributeSorterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\FieldSorterInterface/CatalogBundle\\Doctrine\\Query\\FieldSorterInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ProductQueryBuilderInterface/CatalogBundle\\Doctrine\\Query\\ProductQueryBuilderInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ProductQueryBuilder/CatalogBundle\\Doctrine\\Query\\ProductQueryBuilder/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\CriteriaCondition/CatalogBundle\\Doctrine\\ORM\\Condition\\CriteriaCondition/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\ValueJoin/CatalogBundle\\Doctrine\\ORM\\Join\\ValueJoin/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Doctrine\\ORM\\CompletenessJoin/CatalogBundle\\Doctrine\\ORM\\Join\\CompletenessJoin/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/CatalogBundle\\Entity\\Family/CatalogBundle\\Model\\FamilyInterface/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.event_subscriber.resolve_target_repository/akeneo_storage_utils.event_subscriber.resolve_target_repository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.doctrine.smart_manager_registry/akeneo_storage_utils.doctrine.smart_manager_registry/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.doctrine.table_name_builder/akeneo_storage_utils.doctrine.table_name_builder/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.factory.referenced_collection/akeneo_storage_utils.factory.referenced_collection/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.event_subscriber.mongodb.resolve_target_repositories/akeneo_storage_utils.event_subscriber.mongodb.resolve_target_repository/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.event_subscriber.mongodb.entities_type/akeneo_storage_utils.event_subscriber.mongodb.entities_type/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.event_subscriber.mongodb.entity_type/akeneo_storage_utils.event_subscriber.mongodb.entity_type/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_catalog.mongodb.mongo_objects_factory/akeneo_storage_utils.mongodb.mongo_objects_factory/g'    
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\MongoDB\MongoObjectsFactory/Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\MongoDB\Type\Entities/Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entities/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\MongoDB\Type\Entity/Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entity/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelsPass/Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoriesPass/Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection/Akeneo\Bundle\StorageUtilsBundle\Doctrine\ReferencedCollection/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory/Akeneo\Bundle\StorageUtilsBundle\Doctrine\ReferencedCollectionFactory/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry/Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\Doctrine\TableNameBuilder/Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber/Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\EntityTypeSubscriber/Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntityTypeSubscriber/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\Bundle\CatalogBundle\EventSubscriber\ResolveTargetRepositorySubscriber/Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\ResolveTargetRepositorySubscriber/g'

```

## Upgrade the database

All database upgrade scripts are located in *vendor/akeneo/pim-community-dev/upgrades/1.2-1.3/*. 

Please  note that **these scripts need to be modified if you do not use the regular PIM tables or collections**. This can happen for example, when you override an entity in a custom project, or when you use the [CustomEntityBundle](https://github.com/akeneo/CustomEntityBundle).
 
To help the database migration process, we rely on [DoctrineMigrationsBundle](http://symfony.com/fr/doc/current/bundles/DoctrineMigrationsBundle/index.html). The migration can be launched with `php app/console doctrine:migrations:migrate`.

## Initialize cache and assets

```
 rm app/cache/* -rf
 php app/console cache:clear --env=prod
 php app/console pim:install:assets --env=prod
```

## Products in more than one variant group are not allowed anymore

To detect and remove all products assigned in more than one variant groups, you can run

    php vendor/akeneo/pim-community-dev/upgrades/upgrades/1.2-1.3/common/remove_multiple_variant_groups.php --env=dev

This script will show you the concerned products and generate a CSV file of invalid products.
You can now remove all the irrelevant associations and import the file using the native CSV product import.
