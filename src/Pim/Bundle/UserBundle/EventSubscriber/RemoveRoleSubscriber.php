<?php

namespace Pim\Bundle\UserBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Doctrine\ORM\Query\IsThereUserWithoutRole;
use Pim\Component\User\Exception\ForbiddenToRemoveRoleException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

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

    public function checkIfThereIsUserWithoutRole(RemoveEvent $event)
    {
        $role = $event->getSubject();
        if (!$role instanceof RoleInterface) {
            return;
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
            StorageEvents::PRE_REMOVE => [['checkIfThereIsUserWithoutRole']],
        ];
    }
}
