# UPGRADE FROM 1.2 to 1.3

## General

### Fix BC breaks

If you have a standard installation with some custom code inside, the following command allows to update changed services or use statements.

*It does not cover all possible BC breaks, as the changes of arguments of a service, consider using this script on versioned files to be able to check the changes with a `git diff` for instance*

Based on a pim standard installation, execute the following command in your project folder :

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
```

## CatalogBundle

The ProductQueryBuilder has been re-worked to provide a more solid, extensible and fluent API (cf technical doc).

It's now instanciated from the ProductQueryFactory and it's not anymore a service.

## DataGridBundle

The ProductDatasource has been re-worked to create its own instance of product query builder (PQB).

Product filters and Sorters have been updated to rely on the PQB and avoid to directly manipulate Doctrine QB.

The ProductPersister has been replaced by ProductSaver.


## Akeneo storage

All classes related to Akeneo dual storage (ORM and/or MongoDB) have been moved in a dedicated bundle called *AkeneoStorageUtilsBundle*.

Normally you should not much be impacted by this internal change. The main change concerns the parameter `pim_catalog_storage_driver` that has been deprecated. You are encouraged to replace the parameter `pim_catalog_storage_driver` by `akeneo_storage_utils_storage_driver` in your `app/config/pim_parameters.yml` or `app/config/parameters.yml` configuration file. Please note that the parameter `pim_catalog_storage_driver` is still supported until version 1.4.

Here are the other changes:
 * the following constants have been moved:
  * `DOCTRINE_ORM` and `DOCTRINE_MONGODB_ODM` from `Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension` are now located in `Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension`
  * `DOCTRINE_MONGODB`, `ODM_ENTITIES_TYPE` and `ODM_ENTITY_TYPE` from `Pim\Bundle\CatalogBundle\PimCatalogBundle` are now located in `Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle`
 * the container parameter `pim_catalog.storage_driver` has been renamed to `akeneo_storage_utils.storage_driver`
 * the following services have been renamed:
  * `pim_catalog.event_subscriber.resolve_target_repository` has been renamed to `akeneo_storage_utils.event_subscriber.resolve_target_repository`
  * `pim_catalog.doctrine.smart_manager_registry` has been renamed to `akeneo_storage_utils.doctrine.smart_manager_registry`
  * `pim_catalog.doctrine.table_name_builder` has been renamed to `akeneo_storage_utils.doctrine.table_name_builder`
  * `pim_catalog.factory.referenced_collection` has been renamed to `akeneo_storage_utils.factory.referenced_collection`
  * `pim_catalog.event_subscriber.mongodb.resolve_target_repositories` has been renamed to `akeneo_storage_utils.event_subscriber.mongodb.resolve_target_repository`
  * `pim_catalog.event_subscriber.mongodb.entities_type` has been renamed to `akeneo_storage_utils.event_subscriber.mongodb.entities_type`
  * `pim_catalog.event_subscriber.mongodb.entity_type` has been renamed to `akeneo_storage_utils.event_subscriber.mongodb.entity_type`
  * `pim_catalog.mongodb.mongo_objects_factory` has been renamed to `akeneo_storage_utils.mongodb.mongo_objects_factory`
 * the following classes have been renamed or moved:
  * `Pim\Bundle\CatalogBundle\MongoDB\MongoObjectsFactory` becomes `Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory`
  * `Pim\Bundle\CatalogBundle\MongoDB\Type\Entities` becomes `Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entities`
  * `Pim\Bundle\CatalogBundle\MongoDB\Type\Entity` becomes `Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entity`
  * `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelsPass` becomes `Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass`
  * `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoriesPass` becomes `Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass`
  * `Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection` becomes `Akeneo\Bundle\StorageUtilsBundle\Doctrine\ReferencedCollection`
  * `Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory` becomes `Akeneo\Bundle\StorageUtilsBundle\Doctrine\ReferencedCollectionFactory`
  * `Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry` becomes `Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry`
  * `Pim\Bundle\CatalogBundle\Doctrine\TableNameBuilder` becomes `Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder`
  * `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber` becomes `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber`
  * `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\EntityTypeSubscriber` becomes `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntityTypeSubscriber`
  * `Pim\Bundle\CatalogBundle\EventSubscriber\ResolveTargetRepositorySubscriber` becomes `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\ResolveTargetRepositorySubscriber`
     
Please note that former services, and containter parameter `pim_catalog.storage_driver` are still supported thanks to aliases until version 1.4. 
 
In case you used one the classes or services listed above, you can easily update your code by doing the following:

```
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
