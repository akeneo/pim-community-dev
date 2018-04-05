# 3.0

## Technical improvement

- TIP-236: Merge Oro User bundle/component into Akeneo User bundle/component
- TIP-854: Clean translations (remove useless, duplicate and reorganize keys) 

## Enhancements

- TIP-832: Enable regional languages for UI

## BC breaks

- Move `Pim\Bundle\UserBundle\Entity\UserInterface` to `Pim\Component\User\Model\UserInterface`
- Move `Pim\Bundle\UserBundle\Entity\User` to `Pim\Component\User\Model\User`
- Move `Oro\Bundle\UserBundle\Entity\Group` to `Pim\Component\User\Model\Group`
- Move `Oro\Bundle\UserBundle\Entity\Role` to `Pim\Component\User\Model\Role`
- Move `Oro\Bundle\UserBundle\Entity\UserManager` to `Pim\Bundle\UserBundle\Manager\UserManager`
- Move `Oro\Bundle\UserBundle\OroUserEvents` to `Pim\Component\User\UserEvents`
- Move `Pim\Bundle\UserBundle\Controller\UserGroupRestController` to `Pim\Bundle\UserBundle\Controller\Rest\UserGroupController`
- Move `Pim\Bundle\UserBundle\Controller\SecurityRestController` to `Pim\Bundle\UserBundle\Controller\Rest\SecurityController`
- Move `Pim\Bundle\UserBundle\Controller\UserRestController` to `Pim\Bundle\UserBundle\Controller\Rest\UserController`
- Move all classes from `Oro\Bundle\UserBundle\Controller` to `Pim\Bundle\UserBundle\Controller`
- Move all classes from `Oro\Bundle\UserBundle\EventListener` to `Pim\Bundle\UserBundle\EventListener`
- Move all classes from `Oro\Bundle\UserBundle\Form\EventListener` to `Pim\Bundle\UserBundle\Form\Subscriber`
- Move all classes from `Oro\Bundle\UserBundle\Entity\Repository` to `Pim\Bundle\UserBundle\Doctrine\ORM\Repository`
- Move `Oro\Bundle\UserBundle\Entity\EntityUploadedImageInterface` to `Pim\Component\User\EntityUploadedImageInterface`
- Move `Oro\Bundle\UserBundle\Entity\EventListener\UploadedImageSubscriber` to `Pim\Bundle\UserBundle\EventSubscriber\UploadedImageSubscriber`
- Move `Oro\Bundle\UserBundle\Form\Handler\AbstractUserHandler` to `Pim\Bundle\UserBundle\Form\Handler\AbstractUserHandler`
- Move `Oro\Bundle\UserBundle\Form\Handler\GroupHandler` to `Pim\Bundle\UserBundle\Form\Handler\GroupHandler`
- Move `Oro\Bundle\UserBundle\Form\Type\ChangePasswordType` to `Pim\Bundle\UserBundle\Form\Type\ChangePasswordType`
- Move `Oro\Bundle\UserBundle\Form\Type\GroupApiType` to `Pim\Bundle\UserBundle\Form\Type\GroupApiType`
- Move `Oro\Bundle\UserBundle\Form\Type\GroupType` to `Pim\Bundle\UserBundle\Form\Type\GroupType`
- Move `Oro\Bundle\UserBundle\Form\Type\ResetType` to `Pim\Bundle\UserBundle\Form\Type\ResetType`
- Move `Oro\Bundle\UserBundle\Security\UserProvider` to `Pim\Bundle\UserBundle\Security\UserProvider`

- Merge `Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler` with `Pim\Bundle\UserBundle\Form\Handler\AclRoleHandler`
- Merge `Oro\Bundle\UserBundle\Form\Handler\ResetHandler` with `Pim\Bundle\UserBundle\Form\Handler\ResetHandler`
- Merge `Oro\Bundle\UserBundle\Form\Handler\UserHandler` with `Pim\Bundle\UserBundle\Form\Handler\UserHandler`
- Merge `Oro\Bundle\UserBundle\Form\Type\AclRoleType` with `Pim\Bundle\UserBundle\Form\Type\AclRoleType`
- Merge `Oro\Bundle\UserBundle\Form\Type\RoleApiType` with `Pim\Bundle\UserBundle\Form\Type\RoleApiType`
- Merge `Oro\Bundle\UserBundle\Entity\UserManager` with `Pim\Bundle\UserBundle\Manager\UserManager`

- Remove `Oro\Bundle\UserBundle\OroUserBundle`
- Remove `Oro\Bundle\UserBundle\DependencyInjection`
- Remove `Oro\Bundle\UserBundle\OroUserBundle`
- Remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider`
- Change constructor of `Pim\Bundle\ImportExportBundle\Datagrid\JobDatagridProvider`, remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` argument 
- Change constructor of `Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceFormType`, remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` argument 
- Change constructor of `Pim\Bundle\ImportExportBundle\Normalizer\JobExecutionNormalizer`, remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` argument 
- Change constructor of `Pim\Bundle\ImportExportBundle\Normalizer\StepExecutionNormalizer`, remove `Pim\Bundle\ImportExportBundle\JobLabel\TranslatedLabelProvider` argument  
