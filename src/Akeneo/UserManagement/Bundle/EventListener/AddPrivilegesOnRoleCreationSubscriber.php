<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddPrivilegesOnRoleCreationSubscriber implements EventSubscriberInterface
{
    private AclManager $aclManager;

    public function __construct(AclManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'loadPrivileges',
        ];
    }

    public function loadPrivileges(GenericEvent $event): void
    {
        $role = $event->getSubject();
        if (!$role instanceof RoleInterface || User::ROLE_ANONYMOUS === $role->getRole()) {
            return;
        }
        if (!$event->hasArgument('is_new') || true !== $event->getArgument('is_new')) {
            return;
        }

        $sid = $this->aclManager->getSid($role);
        foreach ($this->aclManager->getAllExtensions() as $extension) {
            $rootOid = $this->aclManager->getRootOid($extension->getExtensionKey());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                $fullAccessMask = $maskBuilder->hasConst('GROUP_SYSTEM')
                    ? $maskBuilder->getConst('GROUP_SYSTEM')
                    : $maskBuilder->getConst('GROUP_ALL');
                $this->aclManager->setPermission($sid, $rootOid, $fullAccessMask, true);
            }

            foreach ($extension->getClasses() as $class) {
                if (!$class->isEnabledAtCreation()) {
                    $oid = new ObjectIdentity($extension->getExtensionKey(), $class->getClassName());
                    $this->aclManager->setPermission($sid, $oid, 0, true);
                }
            }
        }
        $this->aclManager->flush();
    }
}
