# 1.6.x

## Functional improvements

- PIM-5096: Adds XLSX quick export to the product grid and published product grid

## Technical improvements

- PIM-5589: introduce a channels, attribute groups, group types, currencies, locale accesses, asset category accesses, product category accesses, attribute group accesses and job profile accesses import using the new import system introduced in v1.4

## BC breaks

- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct\DetachProductPostPublishSubscriber`. Replace `Pim\Bundle\CatalogBundle\Manager\ProductManager` by `Doctrine\Common\Persistence\ObjectManager`.
- Change constructor of `PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder`. Replace `Pim\Bundle\CatalogBundle\Manager\CurrencyManager` argument by `Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface`.
- Installer fixtures now support csv format for channels setup and not anymore the yml format
- Installer fixtures does not support anymore the yml format for association types
- Installer fixtures now support csv format for attribute groups setup and not anymore the yml format
- Installer fixtures now support csv format for group types setup and not anymore the yml format
- Installer fixtures now support csv format for currencies setup and not anymore the yml format
- Installer fixtures now support csv format for locale accesses setup and not anymore the yml format
- Installer fixtures now support csv format for asset category accesses setup and not anymore the yml format
- Installer fixtures now support csv format for product category accesses setup and not anymore the yml format
- Installer fixtures now support csv format for attribute group accesses setup and not anymore the yml format
- Installer fixtures now support csv format for job profile accesses setup and not anymore the yml format
- Add `Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator` as last parameter of
    `Pim\Component\Connector\ArrayConverter\Flat\AssociationTypeStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeGroupStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeOptionStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\AttributeStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\CategoryStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\ChannelStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\FamilyStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\GroupStandardConverter`,
    `Pim\Component\Connector\ArrayConverter\Flat\ProductStandardConverter,`
    `Pim\Component\Connector\ArrayConverter\Flat\VariantGroupStandardConverter` and
    `Pim\Component\Connector\ArrayConverter\Structured\AttributeOptionStandardConverter`
- Remove deprecated argument $propertyCopier from constructor of `Pim\Component\Catalog\Updater\ProductUpdater` and allow to inject supported fields
- AttributeGroupAccessManager now takes `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository` $repository, `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface` $saver, $attGroupAccessClass as constructor arguments
- LocaleAccessManager now takes `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository` $repository, BulkSaverInterface $saver, $localeClass as constructor arguments
- JobProfileAccessManager now takes `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\JobProfileAccessRepository` $repository, `Akeneo\Component\StorageUtils\Saver\BulkSaverInterface` $saver, $localeClass as constructor arguments
- CategoryAccessManager now takes `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository` $repository, BulkSaverInterface $saver, $categoryClass as constructor arguments
- Move `PimEnterprise\Bundle\SecurityBundle\Model\AccessInterface` to `PimEnterprise\Component\Security\Model\AccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Model\AttributeGroupAccessInterface` to `PimEnterprise\Component\Security\Model\AttributeGroupAccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface` to `PimEnterprise\Component\Security\Model\CategoryAccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Model\JobProfileAccessInterface` to `PimEnterprise\Component\Security\Model\JobProfileAccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Model\LocaleAccessInterface` to `PimEnterprise\Component\Security\Model\LocaleAccessInterface`
- Move `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AccessRepositoryInterface` to `PimEnterprise\Component\Security\Repository\AccessRepositoryInterface`
- `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\LocaleAccessRepository` now implements `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- `PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository\AssetCategoryRepository` now implements `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- `PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository` now implements `Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface`
- Change constructor of `PimEnterprise\Bundle\WorkflowBundle\Controller\PublishedProductController` replace argument `Pim\Bundle\CatalogBundle\Manager\ChannelManager` by `Pim\Component\Catalog\Repository\ChannelRepositoryInterface`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Model` to `PimEnterprise\Component\Workflow\Model`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilderInterface` to `PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Event` to `PimEnterprise\Component\Workflow\Event`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Exception` to `PimEnterprise\Component\Workflow\Exception`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Factory` to `PimEnterprise\Component\Workflow\Factory`.
- Remove class `PimEnterprise\Bundle\WorkflowBundle\Factory\UploadedFileFactory`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Normalizer` to `PimEnterprise\Component\Workflow\Normalizer`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Applier` to `PimEnterprise\Component\Workflow\Applier`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Repository` to `PimEnterprise\Component\Workflow\Repository`.
- Move `PimEnterprise\Bundle\WorkflowBundle\PimEnterprise\Helper\SortProductValuesHelper` to `PimEnterprise\Bundle\WorkflowBundle\Twig\SortProductValuesHelper`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Publisher` to `PimEnterprise\Component\Workflow\Publisher`.
- Move `PimEnterprise\Bundle\WorkflowBundle\Connector\Tasklet` to `PimEnterprise\Component\Workflow\Connector\Tasklet`.
- Move `PimEnterprise\Bundle\CatalogBundle\Model` to `PimEnterprise\Component\Catalog\Model`.
- Move `PimEnterprise\Bundle\SecurityBundle\Attributes` to `PimEnterprise\Component\Security\Attributes`.
- Remove parameter `pimee_workflow.publisher.product_media.class` because class was removed in 1.4.
- Rename and move `PimEnterprise\Bundle\WorkflowBundle\Publisher\Product\FilePublisher` to `PimEnterprise\Component\Workflow\Publisher\Product\FileInfoPublisher`.
