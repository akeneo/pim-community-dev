<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Query\IsThereUserWithoutRole;
use Akeneo\UserManagement\Component\Exception\ForbiddenToRemoveRoleException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveRoleSubscriber implements EventSubscriberInterface
{
    /** @var IsThereUserWithoutRole */
    private $isThereUserWithoutRole;

    public function __construct(IsThereUserWithoutRole $isThereUserWithoutRole)
    {
        $this->isThereUserWithoutRole = $isThereUserWithoutRole;
    }

    public function checkRoleIsRemovable(RemoveEvent $event)
    {
        $role = $event->getSubject();
        if (!$role instanceof Role) {
            return;
        }

        if ($role->getRole() === 'ROLE_USER') {
            throw new ForbiddenToRemoveRoleException('You can not delete this role, this role is the one by default in Akeneo PIM');
        }

        $isThereUserWithoutRole = $this->isThereUserWithoutRole;
        $result = $isThereUserWithoutRole($role->getId());
        if ($result) {
            throw new ForbiddenToRemoveRoleException('You can not delete this role, otherwise some users will no longer have a role.');
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => [['checkRoleIsRemovable']],
        ];
    }
}
