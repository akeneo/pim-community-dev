# 1.6.x

## Technical improvements

- PIM-5589: introduce a channels, attribute groups, group types and currencies import using the new import system introduced in v1.4

## BC breaks

- Installer fixtures now support csv format for channels setup and not anymore the yml format
- Installer fixtures does not support anymore the yml format for association types
- Installer fixtures now support csv format for attribute groups setup and not anymore the yml format
- Installer fixtures now support csv format for group types setup and not anymore the yml format
- Installer fixtures now support csv format for currencies setup and not anymore the yml format
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
