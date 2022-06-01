<?php

declare(strict_types=1);

namespace spec\AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Subscriber;

use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Event\AppUserGroupCreated;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupAttributeGroupPermissionsSaver;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupLocalePermissionsSaver;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Subscriber\AddAllPermissionToAppUserGroup;
use AkeneoEnterprise\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliases;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddAllPermissionToAppUserGroupSpec extends ObjectBehavior
{
    public function let(
        FeatureFlags $featureFlags,
        UserGroupAttributeGroupPermissionsSaver $userGroupAttributeGroupPermissionsSaver,
        UserGroupLocalePermissionsSaver $userGroupLocalePermissionsSaver,
        UserGroupCategoryPermissionsSaver $userGroupCategoryPermissionsSaver
    ) {
        $this->beConstructedWith(
            $featureFlags,
            $userGroupAttributeGroupPermissionsSaver,
            $userGroupLocalePermissionsSaver,
            $userGroupCategoryPermissionsSaver,
        );
    }

    public function it_does_not_add_all_rights_to_app_user_group_if_permissions_are_enabled(
        FeatureFlags $featureFlags,
        UserGroupCategoryPermissionsSaver $userGroupCategoryPermissionsSaver,
        UserGroupLocalePermissionsSaver $userGroupLocalePermissionsSaver,
        UserGroupAttributeGroupPermissionsSaver $userGroupAttributeGroupPermissionsSaver
    ): void
    {
        $featureFlags->isEnabled('permission')->willReturn(true);
        $userGroupCategoryPermissionsSaver->save(Argument::any())->shouldNotBeCalled();
        $userGroupLocalePermissionsSaver->save(Argument::any())->shouldNotBeCalled();
        $userGroupAttributeGroupPermissionsSaver->save(Argument::any())->shouldNotBeCalled();

        $this->addAllPermissions(new AppUserGroupCreated('michel'));
    }


    public function it_adds_all_rights_to_app_user_group_if_permissions_are_disabled(
        FeatureFlags $featureFlags,
        UserGroupCategoryPermissionsSaver $userGroupCategoryPermissionsSaver,
        UserGroupLocalePermissionsSaver $userGroupLocalePermissionsSaver,
        UserGroupAttributeGroupPermissionsSaver $userGroupAttributeGroupPermissionsSaver
    ): void
    {
        $featureFlags->isEnabled('permission')->willReturn(false);
        $userGroupCategoryPermissionsSaver->save(
            'michel',
            [
                'view' => ['all' => true, 'identifiers' => []],
                'edit' => ['all' => true, 'identifiers' => []],
                'own' => ['all' => true, 'identifiers' => []]
            ]
        )->shouldBeCalled();
        $userGroupLocalePermissionsSaver->save(
            'michel',
            [
                'view' => ['all' => true, 'identifiers' => []],
                'edit' => ['all' => true, 'identifiers' => []]
            ]
        )->shouldBeCalled();
        $userGroupAttributeGroupPermissionsSaver->save(
            'michel', [
                'view' => ['all' => true, 'identifiers' => []],
                'edit' => ['all' => true, 'identifiers' => []]
            ]
        )->shouldBeCalled();

        $this->addAllPermissions(new AppUserGroupCreated('michel'));
    }
}
