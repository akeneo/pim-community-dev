<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Exception\ForbiddenToRemoveRoleException;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Storage\Query\GetUserCountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveRoleSubscriber implements EventSubscriberInterface
{
    private GetUserCountInterface $getUserCount;

    public function __construct(GetUserCountInterface $getUserCount)
    {
        $this->getUserCount = $getUserCount;
    }

    public function checkRoleIsRemovable(RemoveEvent $event): void
    {
        $role = $event->getSubject();
        if (!$role instanceof RoleInterface) {
            return;
        }

        if ($role->getRole() === 'ROLE_USER') {
            throw new ForbiddenToRemoveRoleException('You can not delete this role, this role is the one by default in Akeneo PIM');
        }

        if (0 < $this->getUserCount->forUsersHavingOnlyRole($role->getRole())) {
            throw new ForbiddenToRemoveRoleException('You can not delete this role because it is used by either users or connections.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => [['checkRoleIsRemovable']],
        ];
    }
}
