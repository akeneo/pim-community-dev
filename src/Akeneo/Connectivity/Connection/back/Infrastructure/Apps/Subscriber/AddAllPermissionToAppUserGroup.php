<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Connectivity\Connection\Infrastructure\Apps\Subscriber;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Connectivity\Connection\Domain\Apps\Event\AppUserGroupCreated;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupAttributeGroupPermissionsSaver;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupLocalePermissionsSaver;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * When permission are disabled, the app user group has right on everything. This way, the app is granted to do everything,
 * If the permissions of the instance are enabled (due to an edition upgrade for example),
 * it allows to define app permissions with more granularity without any interruption of service or execution of script.
 */
class AddAllPermissionToAppUserGroup implements EventSubscriberInterface
{
    public function __construct(
        private FeatureFlags $featureFlags,
        private UserGroupAttributeGroupPermissionsSaver $userGroupAttributeGroupPermissionsSaver,
        private UserGroupLocalePermissionsSaver $userGroupLocalePermissionsSaver,
        private UserGroupCategoryPermissionsSaver $userGroupCategoryPermissionsSaver
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AppUserGroupCreated::class => 'addAllPermissions'
        ];
    }

    public function addAllPermissions(AppUserGroupCreated $event): void
    {
        if ($this->featureFlags->isEnabled('permission')) {
            return;
        }

        $this->userGroupAttributeGroupPermissionsSaver->save($event->getUserGroupName(), [
            'edit' => [
                'all' => true,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
        $this->userGroupLocalePermissionsSaver->save($event->getUserGroupName(), [
            'edit' => [
                'all' => true,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
        $this->userGroupCategoryPermissionsSaver->save($event->getUserGroupName(), [
            'own' => [
                'all' => true,
                'identifiers' => [],
            ],
            'edit' => [
                'all' => true,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }
}
