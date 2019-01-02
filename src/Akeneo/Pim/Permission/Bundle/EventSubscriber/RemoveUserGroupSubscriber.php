<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Pim\Permission\Component\Exception\ResourceDeletionDeniedException;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntityPermission\SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber called when removing a user group
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class RemoveUserGroupSubscriber implements EventSubscriberInterface
{
    /** @var SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight */
    private $findReferenceEntityWhereUserGroupIsLastToHaveEditRight;

    public function __construct(
        SqlFindReferenceEntityWhereUserGroupIsLastToHaveEditRight $findReferenceEntityWhereUserGroupIsLastToHaveEditRight
    ) {
        $this->findReferenceEntityWhereUserGroupIsLastToHaveEditRight = $findReferenceEntityWhereUserGroupIsLastToHaveEditRight;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'checkUserGroupPermissionsOnReferenceEntity',
        ];
    }

    public function checkUserGroupPermissionsOnReferenceEntity(RemoveEvent $event): void
    {
        $userGroup = $event->getSubject();
        if (!$userGroup instanceof GroupInterface) {
            return;
        }

        $referenceEntityIdentifiers = ($this->findReferenceEntityWhereUserGroupIsLastToHaveEditRight)($userGroup->getId());

        if (count($referenceEntityIdentifiers) > 0) {
            throw new ResourceDeletionDeniedException(
                sprintf(
                    'You cannot delete this group, it is the only user group with "edit" permission on reference entity "%s".',
                    implode('", "', $referenceEntityIdentifiers)
                )
            );
        }
    }
}
