# 1.8.*

## Functional improvements

- API-324: Convert label option to mandatory argument in command `pim:oauth-server:create-client`
- API-312: Add UI to manage (create/revoke) API connections
- TIP-718: Update group types form
- PIM-6291: Adds attribute used as the main picture in the UI for each family (attribute_as_image)
- GITHUB-4877: Update some tooltips messages of the export builder, Cheers @Milie44!
- GITHUB-5949: Fix the deletion of a job instance (import\export) from the job edit page, cheers @BatsaxIV !

## Technical improvements

- TIP-711: Rework job execution reporting page with the new PEF architecture
- TIP-724: Refactoring of the 'Settings\Association types' index screen using 'pim\common\grid'
- TIP-725: Generalization of the refactoring made in the TIP-724 for all screen containing a simple grid
- TIP-734: Menu and index page is now using the new PEF architecture
- GITHUB-6174: Show a loading mask during the file upload in the import jobs
- TIP-730: Reworking of the creation popin for basic entities
- TIP-732: Rework the attribute form using the PEF architecture
- TIP-747: Migrate to Symfony 3.3
- PIM-6740: Separe installation state (installed) from config file 
- API-359: Move notified user of a job into the configuration parameters of the job

## UI\UX Refactoring

- PIM-6288: Update flash messages design
- PIM-6289: Update JSTree design
- PIM-6294: Update switch design
- PIM-6374: Add columns for product navigation
- PIM-6391: Update comments design
- PIM-6403: Update panels design to use dropdown selectors
- PIM-6404: Update buttons design
- PIM-6409: Update all the title containers design
- PIM-6290: Update the main navigation design
- PIM-6397: Enable Search filter on all grids
- PIM-6406: Update job profile show page to include last executions
- TIP-764: Mass edit has been redone
- PIM-6621: Add the ability to search on label and identifier on the product grid

## Remove MongoDB product storage

- Remove container parameter `pim_catalog_product_storage_driver`

- Remove model `src/Pim/Bundle/CatalogBundle/Resources/config/model/doctrine/Association.mongodb.yml`
- Remove model `src/Pim/Bundle/CatalogBundle/Resources/config/model/doctrine/Completeness.mongodb.yml`
- Remove model `src/Pim/Bundle/CatalogBundle/Resources/config/model/doctrine/Product.mongodb.yml`
- Remove model `src/Pim/Bundle/VersioningBundle/Resources/config/model/doctrine/Version.mongodb.yml`

- Remove constants `DOCTRINE_ORM` and `` from `DOCTRINE_MONGODB_ODM` from `Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension`

- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry`
- Remove service `akeneo_storage_utils.doctrine.smart_manager_registry`

- Remove repository `Akeneo\Bundle\ClassificationBundle\Doctrine\Mongo\Repository\AbstractItemCategoryRepository`
- Remove repository `Pim\Bundle\ApiBundle\Doctrine\MongoDBODM\Repository\ProductRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\CompletenessRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductCategoryRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductMassActionRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductRepository`
- Remove repository `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductValueCounterRepository`
- Remove repository `Pim\Bundle\CatalogBundle\spec\Doctrine\MongoDBODM\Repository\CompletenessRepositorySpec`
- Remove repository `Pim\Bundle\CatalogBundle\spec\Doctrine\MongoDBODM\Repository\ProductRepositorySpec`
- Remove repository `Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\VersionRepository`

- Remove event listener `Akeneo\Bundle\StorageUtilsBundle\EventListener\MongoDBODM\ResolveTargetEntityListener`
- Remove event listener `Pim\Bundle\DataGridBundle\EventListener\MongoDB\ConfigureHistoryGridListener`
- Remove event subscriber `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntitiesTypeSubscriber`
- Remove event subscriber `Akeneo\Bundle\StorageUtilsBundle\EventSubscriber\MongoDBODM\EntityTypeSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\EnsureIndexesSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\ProductRelatedEntityRemovalSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\RemoveOutdatedProductsFromAssociationsSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\SetNormalizedProductDataSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\SetProductsSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\TimestampableSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\UpdateNormalizedProductDataSubscriber`
- Remove event subscriber `Pim\Bundle\CatalogBundle\EventSubscriber\ORM\InjectProductReferenceSubscriber`
- Remove event subscriber `Pim\Bundle\VersioningBundle\EventSubscriber\MongoDBODM\AddProductVersionSubscriber`

- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Collections\ReferencedCollectionFactory`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Collections\ReferencedCollection`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor\CursorFactory`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Cursor\Cursor`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\MappingsOverrideConfigurator`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Types\Entities`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\Doctrine\MongoDBODM\Types\Entity`
- Remove class `Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory`
- Remove class `Pim\Bundle\CatalogBundle\Command\CleanMongoDBCommand`
- Remove class `Pim\Bundle\CatalogBundle\Command\MongoDBIndexCreatorCommand`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\CompletenessGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractAttributeFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractFieldFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BooleanFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\CompletenessFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateTimeFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\FamilyFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\GroupsFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MediaFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\NumberFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\OptionFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\OptionsFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\PriceFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\ProductIdFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\StringFilter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexCreator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexPurger`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\AbstractQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\AttributeAsLabelUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\AttributeDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\ChannelDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\FamilyDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\FamilyLabelUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionCodeUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionValueUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\NormalizedDataQueryGeneratorInterface`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\OptionCodeUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\OptionDeletedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\OptionValueUpdatedQueryGenerator`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Saver\ProductSaver`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\BaseSorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\CompletenessSorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\FamilySorter`
- Remove class `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\InGroupSorter`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\AssociationNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\DateTimeNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\GenericNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\MetricNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\ProductNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\ProductPriceNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\ProductValueNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document\VersionNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\AttributeOptionNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\CompletenessNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\DateTimeNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\FamilyNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\FileNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\GroupNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\MetricNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\ProductNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\ProductPriceNormalizer`
- Remove class `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\ProductValueNormalizer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\AssociatedProductHydrator`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\ObjectHydrator`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\ObjectIdHydrator`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\AssociationTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\CompletenessTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\DateTimeTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FamilyTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FieldsTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\GroupsTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\ProductHydrator`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\OptionsTransformer`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\ValuesTransformer`
- Remove class `Pim\Bundle\DataGridBundle\DependencyInjection\Compiler\ResolverPass`
- Remove class `Pim\Bundle\DataGridBundle\Extension\Pager\MongoDbOdm\Pager`
- Remove class `Pim\Bundle\DataGridBundle\Extension\Sorter\MongoDbOdm\FieldSorter`
- Remove class `Pim\Bundle\FilterBundle\Datasource\MongoDbOdm\OdmFilterDatasourceAdapter`
- Remove class `Pim\Bundle\FilterBundle\Datasource\MongoDbOdm\OdmFilterProductDatasourceAdapter`
- Remove class `Pim\Bundle\ReferenceDataBundle\DataGrid\Datasource\ResultRecord\MongoDbOdm\Product\ReferenceDataTransformer`
- Remove class `Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Filter\ReferenceDataFilter`
- Remove class `Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Sorter\ReferenceDataSorter`
- Remove class `Pim\Bundle\ReferenceDataBundle\MongoDB\Normalizer\Document\ReferenceDataNormalizer`
- Remove class `Pim\Bundle\ReferenceDataBundle\MongoDB\Normalizer\NormalizedData\ReferenceDataNormalizer`
- Remove class `Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\BulkVersionBuilder`
- Remove class `Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM\Saver\BulkVersionSaver`
- Remove class `Pim\Bundle\VersioningBundle\UpdateGuesser\MongoDBODM\ContainsProductsUpdateGuesser`
- Remove class `upgrades/UpgradeHelper.php`
- Remove class `Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver`

- Change the constructor of `Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectCodeResolver` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Pim\Bundle\CommentBundle\Controller\CommentController` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Pim\Bundle\VersioningBundle\UpdateGuesser\VariantGroupUpdateGuesser` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Pim\Component\Catalog\Repository\GroupRepositoryInterface` and to remove the `$groupClass` argument
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolver` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Doctrine\Common\Persistence\ObjectManager`
- Change the constructor of `Oro\Bundle\SecurityBundle\Acl\Extension\EntityClassResolver` to replace `Doctrine\Common\Persistence\ManagerRegistry` by `Symfony\Bridge\Doctrine\RegistryInterface\RegistryInterface`
- Change the constructor of `Pim\Bundle\DataGridBundle\EventListener\ConfigureSortersListener` to remove `Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datasource\DatasourceAdapterResolver` to remove `Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver`

## BC breaks

### Classes

- Remove class `Pim\Bundle\EnrichBundle\Form\Type\AttributeRequirementType`
- PIM-6442: Rename `Pim\Bundle\VersioningBundle\Normalizer\Flat\AbstractProductValueDataNormalizer` to `Pim\Bundle\VersioningBundle\Normalizer\Flat\AbstractValueDataNormalizer`
- PIM-6442: Rename `Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductValueNormalizer` to `Pim\Bundle\VersioningBundle\Normalizer\Flat\ValueNormalizer`
- PIM-6442: Rename `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteChecker` to `Pim\Component\Catalog\Completeness\Checker\ValueCompleteChecker`
- PIM-6442: Rename `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface` to `Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\DateProductValueFactory` to `Pim\Component\Catalog\Factory\Value\DateValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\MediaProductValueFactory` to `Pim\Component\Catalog\Factory\Value\MediaValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\MetricProductValueFactory` to `Pim\Component\Catalog\Factory\Value\MetricValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\OptionProductValueFactory` to `Pim\Component\Catalog\Factory\Value\OptionValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\OptionsProductValueFactory` to `Pim\Component\Catalog\Factory\Value\OptionsValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\PriceCollectionProductValueFactory` to `Pim\Component\Catalog\Factory\Value\PriceCollectionValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface` to `Pim\Component\Catalog\Factory\Value\ValueFactoryInterface`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValue\ScalarProductValueFactory` to `Pim\Component\Catalog\Factory\Value\ScalarValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValueCollectionFactory` to `Pim\Component\Catalog\Factory\ProductValueCollectionFactory`
- PIM-6442: Rename `Pim\Component\Catalog\Factory\ProductValueFactory` to `Pim\Component\Catalog\Factory\ValueFactory`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\DateProductValue` to `Pim\Component\Catalog\Value\DateValue`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\MediaProductValue` to `Pim\Component\Catalog\Value\MediaValue`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\MetricProductValue` to `Pim\Component\Catalog\Value\MetricValue`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\OptionProductValue` to `Pim\Component\Catalog\Value\OptionValue`
- PIM-6442: Rename `Pim\Component\Catalog\ProductValue\OptionsProductValue` to `Pim\Component\Catalog\Value\OptionsValue`
- PIM-6442: Rename `Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductValue` to `Pim\Component\Connector\ArrayConverter\FlatToStandard\Value`
- PIM-6442: Rename `Pim\Component\Enrich\Converter\EnrichToStandard\ProductValueConverter` to `Pim\Component\Enrich\Converter\EnrichToStandard\ValueConverter`
- PIM-6442: Rename `Pim\Component\Enrich\Converter\StandardToEnrich\ProductValueConverter` to `Pim\Component\Enrich\Converter\StandardToEnrich\ValueConverter`
- PIM-6442: Rename `Pim\Component\ReferenceData\Factory\ProductValue\ReferenceDataCollectionProductValueFactory` to `Pim\Component\ReferenceData\Factory\Value\ReferenceDataCollectionValueFactory`
- PIM-6442: Rename `Pim\Component\ReferenceData\Factory\ProductValue\ReferenceDataProductValueFactory` to `Pim\Component\ReferenceData\Factory\Value\ReferenceDataValueFactory`
- PIM-6442: Rename `Pim\Component\ReferenceData\ProductValue\ReferenceDataCollectionProductValue` to `Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue`
- PIM-6442: Rename `Pim\Component\ReferenceData\ProductValue\ReferenceDataProductValue` to `Pim\Component\ReferenceData\Value\ReferenceDataValue`
- PIM-6442: Rename `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductValueValueFactoryPass` to `Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterValueFactoryPass`
- TIP-764: Remove `Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface` and all inherited classes
- PIM-6740: Remove `Pim\Bundle\InstallerBundle\Persister\YamlPersister`

### Constructors

- Change the constructor of `Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Oro\Bundle\DataGridBundle\Datagrid\RequestParameters` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\GroupColumnsConfigurator` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datagrid\Request\RequestParametersExtractor` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Bundle\DataGridBundle\EventListener\AddParametersToProductGridListener` to add `Symfony\Component\HttpFoundation\RequestStack`
- Change the constructor of `Pim\Component\Catalog\Updater\AssociationTypeUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Component\Catalog\Updater\ChannelUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\ChannelController` to add `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`,
 `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`, `Symfony\Component\Validator\Validator\ValidatorInterface`,`Symfony\Component\Routing\RouterInterface`,
  `Pim\Bundle\ApiBundle\Stream\StreamResourceResponse` and `Akeneo\Component\StorageUtils\Saver\SaverInterface` before last parameter
- Change the constructor of `Pim\Component\Connector\Writer\Database\ProductWriter` to replace `Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface` by `Akeneo\Component\StorageUtils\Cache\CacheClearerInterface`.
- Change the constructor of `Pim\Component\Catalog\Updater\AttributeGroupUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\JobTrackerController` to add `Oro\Bundle\SecurityBundle\SecurityFacade` and add an associative array
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\ProductController` to remove `Pim\Component\Api\Pagination\PaginatorInterface`
- Change the constructor of `Pim\Component\Catalog\Manager\CompletenessManager` to remove the completeness class.
- Change the constructor of `Pim\Component\Catalog\Updater\FamilyUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Component\Catalog\Updater\AttributeUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add `kernel.logs_dir`
- Change the constructor of `Pim\Bundle\EnrichBundle\Twig\AttributeExtension` to remove `pim_enrich.attribute_icons`
- Remove OroNotificationBundle
- Remove createAction from `Pim\Bundle\EnrichBundle/Controller/AssociationTypeController.php`
- Remove `Pim\Bundle\EnrichBundle\Controller\FamilyController.php`
- Remove `Pim\Bundle\EnrichBundle\Controller\VariantGroupController.php`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeGroupController` to add `Oro\Bundle\SecurityBundle\SecurityFacade`, `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`, `Symfony\Component\Validator\ValidatorInterface`, `Akeneo\Component\StorageUtils\Saver\SaverInterface`, `Akeneo\Component\StorageUtils\Remover\RemoverInterface`, `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements` to remove `Pim\Component\Catalog\Repository\AttributeRepositoryInterface` and remove `Pim\Component\Catalog\Factory\AttributeRequirementFactory`
- Change the constructor of `Pim\Bundle\ApiBundle\EventSubscriber\CheckHeadersRequestSubscriber` to add `Pim\Bundle\ApiBundle\Negotiator\ContentTypeNegotiator`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator\MultipleOptionValueUpdatedQueryGenerator` to add `Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData\AttributeOptionNormalizer`
- Change the constructor of `Pim\Component\Catalog\Model\AbstractMetric` to replace `id` by `family`, `unit`, `data`, `baseUnit` and `baseData` (strings)
- Change the constructor of `Pim\Component\Catalog\Factory\MetricFactory` to add `Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter` and `Akeneo\Bundle\MeasureBundle\Manager\MeasureManager`
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValue\MetricDenormalizer` to remove `Akeneo\Component\Localization\Localizer\LocalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Converter\MetricConverter` to add `Pim\Component\Catalog\Builder\ProductBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Denormalizer\Standard\ProductValue\PricesDenormalizer` to remove `Akeneo\Component\Localization\Localizer\LocalizerInterface` and replace `"Pim\Component\Catalog\Model\ProductPrice"` `Pim\Component\Catalog\Factory\PriceFactory`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\MultiSelectAttributeAdder` to remove `Pim\Component\Catalog\Validator\AttributeValidatorHelper`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\PriceCollectionAttributeAdder` to remove `Pim\Component\Catalog\Validator\AttributeValidatorHelper`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\AttributeCopier` and `Pim\Component\Catalog\Updater\Copier\MetricAttributeCopier` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as third argument
- Change the constructor of `Pim\Component\Catalog\Manager\ProductTemplateMediaManager` to replace `Symfony\Component\Serializer\Normalizer\NormalizerInterface` by `Pim\Component\Catalog\Factory\ProductValueFactory`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\MultiSelectAttributeRemover` to replace `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` by `Pim\Component\Catalog\Factory\ProductValueFactory`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\PriceCollectionAttributeRemover` to add `Pim\Component\Catalog\Factory\ProductValueFactory` as third argument
- Change the constructor of `Pim\Component\Catalog\Model\AbstractProductValue` to add `Pim\Component\Catalog\Model\AttributeInterface`, `channel` (string), `locale` (string), `data` (mixed)
- Change the constructor of `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\PricesDenormalizer` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as third parameter
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\FamilyFilter` to remove `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface`
- Change the constructor of `Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\GroupsFilter` to remove `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductTemplateBuilder` to remove first argument `Symfony\Component\Serializer\Normalizer\NormalizerInterface`, second argument `Symfony\Component\Serializer\Normalizer\DenormalizerInterface`, and last argument `%pim_catalog.entity.product.class%`
- Change the constructor of `Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer` to remove `Symfony\Component\Serializer\Normalizer\DenormalizerInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\ProductTemplateUpdater` to add `Symfony\Component\Serializer\Normalizer\NormalizerInterface` as second argument
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\ProductController` to remove
    `Pim\Component\Catalog\Manager\CompletenessManager`,
    `Doctrine\Common\Persistence\ObjectManager`,
    `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`,
    `Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface` and
    `$storageDriver`, and add `Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface`
- Change the constructor of `Pim\Component\Connector\Writer\File\ProductColumnSorter` to replace `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` by `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepositoryInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\VariantGroupUpdater` to replace `Pim\Component\Catalog\BuilderProductBuilderInterface` and `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`
    by `Pim\Component\Catalog\Factory\ProductValueFactory`, `Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface` and `Akeneo\Component\FileStorage\File\FileStorerInterface`
- Change the constructor of `Pim\Component\Connector\Processor\Normalization\VariantGroupProcessor` to remove `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface`
- Change the constructor of `Pim\Bundle\DataGridBundle\Extension\Sorter\Product\ValueSorter` to add `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datasource\ProductDatasource` to remove `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductBuilder` to add `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\ProductUpdater` to add a `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface` as the 3rd argument.
- Change the constructor of `Pim\Component\Catalog\Converter\MetricConverter` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\AbstractAttributeAdder` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\MultiSelectAttributeAdder` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Adder\PriceCollectionAttributeAdder` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Setter\AbstractAttributeSetter` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Setter\AttributeSetter` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Setter\MediaAttributeSetter` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\AbstractAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\AttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\MediaAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Copier\MetricAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\ReferenceData\Updater\Copier\ReferenceDataAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\ReferenceData\Updater\Copier\ReferenceDataCollectionAttributeCopier` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\PriceCollectionAttributeRemover` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Component\Catalog\Updater\Remover\MultiSelectAttributeRemover` to replace `Pim\Component\Catalog\Builder\ProductBuilderInterface` by `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\AttributeController`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\AttributeNormalizer` to add `Pim\Bundle\VersioningBundle\Manager\VersionManager`, `Symfony\Component\Serializer\Normalizer\NormalizerInterface`, `Pim\Bundle\EnrichBundle\Provider\StructureVersion\StructureVersionProviderInterface`, `Akeneo\Component\Localization\Localizer\LocalizerInterface`
- Change the constructor of `Pim\Bundle\EnrichBundle\Normalizer\ProductNormalizer` to add `Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer`
- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\Rest\JobInstanceController` to add `uploadTmpDir` (string)
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\ProductController` to remove `Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository`
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\ProductController` to add `Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface`
- Change the constructor of `Pim\Component\Catalog\Builder\ProductBuilder` to replace `Pim\Component\Catalog\Manager\AttributeValuesResolver` by `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface` 
- Change the constructor of `Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver` to replace `Pim\Component\Catalog\Manager\AttributeValuesResolver` by `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface` 
- Change the constructor of `Pim\Component\Catalog\Builder\EntityWithValuesBuilder` to replace `Pim\Component\Catalog\Manager\AttributeValuesResolver` by `Pim\Component\Catalog\Manager\AttributeValuesResolverInterface` 
- Change the constructor of `Pim\Bundle\ApiBundle\Controller\LocaleController` to add `Pim\Bundle\ApiBundle\Checker\QueryParametersCheckerInterface`
- Change the constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add `Akeneo\Component\Batch\Job\JobParametersValidator`
- Change the constructor of `Pim\Bundle\ConnectorBundle\Launcher\AuthenticatedJobLauncher` to add `Akeneo\Component\Batch\Job\JobParametersValidator`

### Methods

- Change `Pim\Component\Catalog\Model\FamilyInterface` to add `setAttributeAsImage` and `getAttributeAsImage`

### Type hint

- Add type hint `Akeneo\Component\Batch\Model\JobExecution` to the return of the function `launch` of `Akeneo\Bundle\BatchBundle\Launcher`
- Add type hint `array` to the return of the function `resolveEligibleValues` of `Pim\Component\Catalog\Manager\AttributeValuesResolver`

### Others

- Add method `findCategoriesItem` to `Akeneo\Component\Classification\Repository\ItemCategoryRepositoryInterface`
- Add method `getAssociatedProductIds` to `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove useless method `applyFilterByIds` of `Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface`
- Remove useless method `getLocalesQB` of `Pim\Component\Catalog\Repository\LocaleRepositoryInterface`
- Remove useless method `findTypeIds` of `Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface`
- Remove useless methods `getChoicesByType`, `countVariantGroups`, `getVariantGroupsByIds`, `getAllVariantGroupIds` and `getVariantGroupsByAttributeIds` of `Pim\Component\Catalog\Repository\GroupRepositoryInterface`
- Remove useless method `findAttributeIdsFromFamilies` of `Pim\Component\Catalog\Repository\FamilyRepositoryInterface`
- Change visibility from public to protected of `getActivatedCurrenciesQB` method of `Pim\Component\Catalog\Repository\CurrencyRepositoryInterface`
- Remove useless methods `findAllWithTranslations` and `getAttributeGroupsFromAttributeCodes` of `Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface`
- Remove useless method `countForAssociationType` of `Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface`
- Remove useless methods `countChildren` and `search` of `Akeneo\Component\Classification\Repository\CategoryRepositoryInterface`
- Remove useless methods `buildByChannelAndCompleteness`, `setAttributeRepository` and `getObjectManager`  of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove useless methods `findWithGroups` and `getNonIdentifierAttributes` of `Pim\Component\Catalog\Repository\AttributeRepositoryInterface`
- Remove OroNotificationBundle
- Extract and rename method `valueExists` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface` into `Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface`::`uniqueDataExistsInAnotherProduct`.
- Remove methods `searchAfterOffset`, `searchAfterIdentifier` and `count` of `Pim\Component\Api\Repository\ProductRepositoryInterface`
- Extract methods `schedule*` of `Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface` into a `Pim\Component\Catalog\Completeness\CompletenessRemoverInterface`. Methods `schedule`, `scheduleForFamily` and `scheduleForChannelAndLocale` have been renamed respectively `removeForProduct`, `removeForFamily` and `removeForChannelAndLocale`.
- Remove method `findOneById` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`.
- Move class `Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\DummyFilter` to `Pim\Bundle\EnrichBundle\ProductQueryBuilder\Filter\DummyFilter` as this filter is just for UI concerns
- Rename class `Pim\Component\Catalog\Completeness\Checker\ChainedProductValueCompleteChecker`  to `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteChecker`
- Change the method `isComplete` of `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface` to make `Pim\Component\Catalog\Model\ChannelInterface` and `Pim\Component\Catalog\Model\LocaleInterface` mandatory.
- Change the method `supportsValue` of `Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface` to add `Pim\Component\Catalog\Model\ChannelInterface` and `Pim\Component\Catalog\Model\LocaleInterface`.
- Remove class `Pim\Component\Catalog\Completeness\Checker\EmptyChecker`
- Remove classes `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AbstractEntityDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AssociationDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\CategoryDenormalizer`,
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\FamilyDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\GroupDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\AssociationDenormalizer`,
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValueDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValuesDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\BaseValueDenormalizer`,
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\AttributeOptionsDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\PricesDenormalizer`
    `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\MetricDenormalizer`, `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\DateTimeDenormalizer` and `Pim\Bundle\VersioningBundle\Denormalizer\Flat\ProductValue\FileDenormalizer`
- Remove service parameters: `pim_serializer.denormalizer.flat.family.class`, `pim_serializer.denormalizer.flat.category.class`, `pim_serializer.denormalizer.flat.group.class`, `pim_serializer.denormalizer.flat.association.class`,
    `pim_serializer.denormalizer.flat.product_value.class`, `pim_serializer.denormalizer.flat.product_values.class`, `pim_serializer.denormalizer.flat.base_value.class`, `pim_serializer.denormalizer.flat.attribute_option.class`,
    `pim_serializer.denormalizer.flat.attribute_options.class`, `pim_serializer.denormalizer.flat.prices.class`, `pim_serializer.denormalizer.flat.metric.class`, `pim_serializer.denormalizer.flat.datetime.class`
    and `pim_serializer.denormalizer.flat.file.class`
- Remove method `getFullProduct` and `findOneByWithValues` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Rename method `getEligibleProductIdsForVariantGroup` to `getEligibleProductsForVariantGroup` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`. And returns a `Akeneo\Component\StorageUtils\Cursor\CursorInterface`.
- Remove methods `getFullProduct` and `findOneByWithValues` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface`
- Remove class `Pim\Bundle\VersioningBundle\UpdateGuesser\ProductValueUpdateGuesser.php`
- Remove service and parameter: `pim_pim_versioning.update_guesser.product_value` and `pim_versioning.update_guesser.product_value.class`
- Add method `setValues` and `setIdentifier` to `Pim\Component\Catalog\Model\ProductInterface`
- Remove method `setNormalizedData` from `Pim\Component\Catalog\Model\ProductInterface`
- Change method `fetchAll` of `Pim\Component\Connector\Processor\BulkMediaFetcher` to use a `Pim\Component\Catalog\Model\ProductValueCollectionInterface` instead of an `Doctrine\Common\Collections\ArrayCollection`
- Remove method `markIndexedValuesOutdated` from `Pim\Component\Catalog\Model\ProductInterface` and `Pim\Component\Catalog\Model\AbstractProduct`
- Remove classes `Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM\MetricBaseValuesSubscriber` and `Pim\Bundle\CatalogBundle\EventSubscriber\ORM\MetricBaseValuesSubscriber`
- Remove service `pim_catalog.event_subscriber.metric_base_values`
- Remove method `setId`, `getId`, `setValue`, `getValue`, `setBaseUnit`, `setUnit`, `setBaseData`, `setData` and `setFamily` from `Pim\Component\Catalog\Model\MetricInterface`
- Add method `isEqual` to `Pim\Component\Catalog\Model\MetricInterface`
- Add a new argument `$amount` (string) to `addPriceForCurrency` method of `Pim\Component\Catalog\Builder\ProductBuilderInterface`
- Remove methods `setId`, `getId`, `setValue`, `getValue`, `setCurrency` and `setData` from `Pim\Component\Catalog\Model\ProductPriceInterface`
- Add method `isEqual` to `Pim\Component\Catalog\Model\ProductPriceInterface`
- Add a new argument `$data` to `addProductValue` method of `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove methods `createProductValue`, `addProductValue`, `addPriceForCurrencyWithData` and `removePricesNotInCurrency` from `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove classes `Pim\Component\Catalog\Updater\Setter\TextAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\MetricAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\BooleanAttributeSetter`,
    `Pim\Component\Catalog\Updater\Setter\DateAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\NumberAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\SimpleSelectAttributeSetter`,
    `Pim\Component\Catalog\Updater\Setter\MultiSelectAttributeSetter`, `Pim\Component\Catalog\Updater\Setter\PriceCollectionAttributeSetter`, `Pim\Component\ReferenceData\Updater\Setter\ReferenceDataSetter`,
    `Pim\Component\ReferenceData\Updater\Setter\ReferenceDataCollectionSetter`
- Add `Pim\Component\Catalog\Updater\Setter\AttributeSetter`
- Remove classes `Pim\Component\Catalog\Updater\Copier\SimpleSelectAttributeCopier`, `Pim\Component\Catalog\Updater\Copier\MultiSelectAttributeCopier` and `Pim\Component\Catalog\Updater\Copier\PriceCollectionAttributeCopier`
- Rename class `Pim\Component\Catalog\Updater\Copier\BaseAttributeCopier` in `Pim\Component\Catalog\Updater\Copier\AttributeCopier`
- Remove methods `addPriceForCurrency` and `addMissingPrices` from `Pim\Component\Catalog\BuilderProductBuilderInterface`
- Remove methods `getId`, `setId`, `getProduct`, `getEntity`, `setProduct`, `setEntity`, `addOption`, `addPrice`, `removePrice`, `RemoveOption`, `addData` and `isRemovable` from `Pim\Component\Catalog\Model\ProductValueInterface` and `Pim\Component\Catalog\Model\AbstractProductValue`
- Remove methods `setData`, `setText`, `setDecimal`, `setOptions`, `setOption`, `setPrices`, `setPrice`, `setBoolean`, `setVarchar`, `setMedia`, `setMetric`, `setScope`, `setLocale`, `setDate` and `setDatetime` from `Pim\Component\Catalog\Model\ProductValueInterface`
    and make them protected in `Pim\Component\Catalog\Model\AbstractProductValue`
- Remove useless class `Pim\Component\Catalog\Validator\ConstraintGuesser\IdentifierGuesser`
- Remove useless service and parameter `pim_catalog.validator.constraint_guesser.identifier` and `pim_catalog.validator.constraint_guesser.identifier.class`
- Remove third argument `$locale` from `addAttributes` method of `Pim\Component\Catalog\Builder\ProductTemplateBuilderInterface`
- Make protected the method `setValues` in `Pim\Component\Catalog\Updater\VariantGroupUpdater`
- Add method `getId` and remove `setMissingCount`, `setChannel`, `setLocale`, `setProduct`, `setRequiredCount` from `Pim\Component\Catalog\Model\CompletenessInterface` and `Pim\Component\Catalog\Model\AbstractCompleteness`
- Remove useless classes `Pim\Bundle\EnrichBundle\Controller\CompletenessController`
- Remove useless service `pim_enrich.controller.completeness` and parameter `pim_enrich.controller.completeness.class`
- Remove class `Pim\Bundle\EnrichBundle\Controller\Rest\CompletenessController`
- Remove service `pim_enrich.controller.rest.completeness` and parameter `pim_enrich.controller.rest.completeness.class`
- Add method `findCodesByIdentifiers` in `Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface`
- Add method `findCodesByIdentifiers` in `Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface`
- Remove class `Pim\Bundle\DataGridBundle\EventListener\AddParametersToVariantProductGridListener`
- Remove methods `createVariantGroupDatagridQueryBuilder` and `createGroupDatagridQueryBuilder` from `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository`
- Extract and rename method `valueExists` of `Pim\Component\Catalog\Repository\ProductRepositoryInterface` into `Pim\Component\Catalog\Repository\ProductUniqueDataRepositoryInterface`::`uniqueDataExistsInAnotherProduct`.
- Remove class `Pim\Bundle\DataGridBundle\Datasource\ResultRecord\Orm\ProductHydrator`
- Remove services `pim_datagrid.datasource.result_record.hydrator.product` and `pim_datagrid.datasource.result_record.hydrator.associated_product`
    and parameters `pim_datagrid.datasource.result_record.hydrator.product.class` and `pim_datagrid.datasource.result_record.hydrator.associated_product.class`
- Remove all standard denormalizers classes `Pim\Component\Catalog\Denormalizer\Standard\*` and services `pim_catalog.denormalizer.standard.*`
- Add argument `Pim\Component\Catalog\Model\ProductInterface` to `addValue` method of `Pim\Component\Catalog\Validator\UniqueValueSet`
- Remove OroNavigationBundle
- Remove OroNotificationBundle
- Remove `Pim\Bundle\EnrichBundle\Controller\FamilyController.php`
- Remove unused `Pim\Component\Catalog\Manager\AttributeGroupManager`
- Remove unused `Pim\Bundle\CatalogBundle\ProductQueryUtility`
- Remove service `pim_installer.yaml_persister`

### Methods

- Remove `attributeIcon` method from `Pim\Bundle\EnrichBundle\Twig\AttributeExtension`
- Remove the `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface` from `Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AssociationRepository`
- Rename `BackendType::TEXT = 'text'` to `BackendType::TEXTEAREA = 'textarea'` and `BackendType::VARCHAR = 'varchar'` to `BackendType::TEXT = 'text'` from `Pim\Component\Catalog\AttributeTypes`
- Remove methods `addAttributeToProduct` and `addOrReplaceProductValue` from `Pim\Component\Catalog\Builder\ProductBuilderInterface`.
    These methods are now in `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface` and have been renamed to `addAttribute` and `addOrReplaceValue`.
    For both methods, the `Pim\Component\Catalog\Model\ProductInterface` has been replaced by `Pim\Component\Catalog\Model\EntityWithValuesInterface`.
- Remove methods `getRawValues`, `setRawValues`, `getValues`, `setValues`, `getValue`, `addValue`, `removeValue`, `getAttributes`, `hasAttribute` and `getUsedAttributeCodes` from `Pim\Component\Catalog\Model\ProductInterface`.
    These methods are now in the `Pim\Component\Catalog\Model\EntityWithValuesInterface`.
- Change method `convert` of `Pim\Component\Catalog\Converter\MetricConverter` to use `Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface` instead of a `Pim\Component\Catalog\Builder\ProductBuilderInterface`.
- Change method `addAttributeData` of `Pim\Component\Catalog\Updater\Adder\AttributeAdderInterface` to use a `Pim\Component\Catalog\Model\EntityWithValuesInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`.
- Change method `copyAttributeData` of `Pim\Component\Catalog\Updater\Copier\AttributeCopierInterface` to use 2 `Pim\Component\Catalog\Model\EntityWithValuesInterface` instead of 2 `Pim\Component\Catalog\Model\ProductInterface`.
- Change method `removeAttributeData` of `Pim\Component\Catalog\Updater\Remover\AttributeRemoverInterface` to use a `Pim\Component\Catalog\Model\EntityWithValuesInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`.
- Change method `setAttributeData` of `Pim\Component\Catalog\Updater\Setter\AttributeSetterInterface` to use a `Pim\Component\Catalog\Model\EntityWithValuesInterface` instead of a `Pim\Component\Catalog\Model\ProductInterface`.
- Rename class `pim_catalog.factory.product_value_collection.class` to `pim_catalog.factory.value_collection.class`
- Rename class `pim_catalog.factory.product_value.class` to `pim_catalog.factory.value.class`
- Rename class `pim_catalog.factory.product_value.scalar.class` to `pim_catalog.factory.value.scalar.class`
- Rename class `pim_catalog.factory.product_value.metric.class` to `pim_catalog.factory.value.metric.class`
- Rename class `pim_catalog.factory.product_value.price_collection.class` to `pim_catalog.factory.value.price_collection.class`
- Rename class `pim_catalog.factory.product_value.option.class` to `pim_catalog.factory.value.option.class`
- Rename class `pim_catalog.factory.product_value.options.class` to `pim_catalog.factory.value.options.class`
- Rename class `pim_catalog.factory.product_value.media.class` to `pim_catalog.factory.value.media.class`
- Rename class `pim_catalog.factory.product_value.date.class` to `pim_catalog.factory.value.date.class`
- Rename class `pim_serializer.normalizer.flat.product_value.class` to `pim_serializer.normalizer.flat.value.class`
- Rename class `pim_catalog.entity.product_value.scalar.class` to `pim_catalog.entity.value.scalar.class`
- Rename class `pim_catalog.entity.product_value.media.class` to `pim_catalog.entity.value.media.class`
- Rename class `pim_catalog.entity.product_value.metric.class` to `pim_catalog.entity.value.metric.class`
- Rename class `pim_catalog.entity.product_value.option.class` to `pim_catalog.entity.value.option.class`
- Rename class `pim_catalog.entity.product_value.options.class` to `pim_catalog.entity.value.options.class`
- Rename class `pim_catalog.entity.product_value.date.class` to `pim_catalog.entity.value.date.class`
- Rename class `pim_catalog.entity.product_value.price_collection.class` to `pim_catalog.entity.value.price_collection.class`
- Rename class `pim_enrich.converter.standard_to_enrich.product_value.class` to `pim_enrich.converter.standard_to_enrich.value.class`
- Rename class `pim_enrich.converter.enrich_to_standard.product_value.class` to `pim_enrich.converter.enrich_to_standard.value.class`
- Rename class `pim_reference_data.factory.product_value.reference_data.class` to `pim_reference_data.factory.value.reference_data.class`
- Rename class `pim_reference_data.factory.product_value.reference_data_collection.class` to `pim_reference_data.factory.value.reference_data_collection.class`
- Rename class `pim_reference_data.product_value.reference_data.class` to `pim_reference_data.value.reference_data.class`
- Rename class `pim_reference_data.product_value.reference_data_collection.class` to `pim_reference_data.value.reference_data_collection.class`
- Rename service `pim_catalog.factory.product_value` to `pim_catalog.factory.value`
- Rename service `pim_catalog.factory.product_value_collection` to `pim_catalog.factory.value_collection`
- Rename service `pim_catalog.factory.product_value.text` to `pim_catalog.factory.value.text`
- Rename service `pim_catalog.factory.product_value.textarea` to `pim_catalog.factory.value.textarea`
- Rename service `pim_catalog.factory.product_value.number` to `pim_catalog.factory.value.number`
- Rename service `pim_catalog.factory.product_value.boolean` to `pim_catalog.factory.value.boolean`
- Rename service `pim_catalog.factory.product_value.identifier` to `pim_catalog.factory.value.identifier`
- Rename service `pim_catalog.factory.product_value.metric` to `pim_catalog.factory.value.metric`
- Rename service `pim_catalog.factory.product_value.price_collection` to `pim_catalog.factory.value.price_collection`
- Rename service `pim_catalog.factory.product_value.option` to `pim_catalog.factory.value.option`
- Rename service `pim_catalog.factory.product_value.options` to `pim_catalog.factory.value.options`
- Rename service `pim_catalog.factory.product_value.file` to `pim_catalog.factory.value.file`
- Rename service `pim_catalog.factory.product_value.image` to `pim_catalog.factory.value.image`
- Rename service `pim_catalog.factory.product_value.date` to `pim_catalog.factory.value.date`
- Rename service `pim_catalog.model.product_value.interface` to `pim_catalog.model.value.interface`
- Rename service `pim_versioning.serializer.normalizer.flat.product_value` to `pim_versioning.serializer.normalizer.flat.value`
- Remove interface `Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeInterface`, attribute type classes must now implement directly `Pim\Component\Catalog\AttributeTypeInterface`
- Remove class `Pim\Bundle\EnrichBundle\Controller\AttributeController`
- Remove service `pim_enrich.controller.attribute`
- Remove several UI related classes for attributes: `Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber`, `Pim\Bundle\EnrichBundle\Form\Type\AttributeProperty\AvailableLocalesType`, `Pim\Bundle\EnrichBundle\Form\Type\AttributeProperty\OptionsType`, `Pim\Bundle\EnrichBundle\Form\Type\AttributeType`
- Remove services `pim_enrich.form.subscriber.attribute`, `pim_enrich.form.type.attribute`, `pim_enrich.form.type.available_locales`, `pim_enrich.form.type.options`, `pim_enrich.form.attribute`, `pim_enrich.form.handler.attribute`
- Add subscriber to lock/unlock batch job commands thanks to @bOnepain

## Requirements

- GITHUB-5937: Remove the need to have mcrypt installed

## Bug Fixes

- GITHUB-6101: Fix Summernote (WYSIWYG) style
- GITHUB-6337: Write invalid items process fails when it encounters a Date field in xlsx files thanks to @pablollorens!
