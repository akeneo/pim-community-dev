# 1.6.x

## Technical improvements

- PIM-5589: introduce a channels import using the new import system introduced in v1.4 

## BC breaks

- Installer fixtures now support csv format for channels setup and not anymore the yml format
- Installer fixtures does not support anymore the yml format for association types
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
