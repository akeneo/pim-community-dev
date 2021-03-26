<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Adds default privileges to a newly created role
 */
class AddDefaultPrivilegesSubscriber implements EventSubscriberInterface
{
    private const FIXTURE_ROLE_JOB_NAME = 'fixtures_user_role_csv';

    private ObjectRepository $roleRepository;
    private AclManager $aclManager;

    public function __construct(ObjectRepository $roleRepository, AclManager $aclManager)
    {
        $this->roleRepository = $roleRepository;
        $this->aclManager = $aclManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURE => 'loadDefaultPrivilegesForAllRoles',
            StorageEvents::POST_SAVE => 'loadDefaultPrivilegesAfterSave',
        ];
    }

    public function loadDefaultPrivilegesForAllRoles(InstallerEvent $event): void
    {
        if (!$event->hasArgument('job_name') || static::FIXTURE_ROLE_JOB_NAME !== $event->getArgument('job_name')) {
            return;
        }

        $roles = $this->roleRepository->findAll();
        foreach ($roles as $role) {
            $this->loadDefaultPrivilegesForRole($role);
        }
    }

    public function loadDefaultPrivilegesAfterSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof RoleInterface) {
            return;
        }

        if (!$event->hasArgument('is_fixture') || !$event->getArgument('is_fixture')) {
            return;
        }

        $this->loadDefaultPrivilegesForRole($subject);
    }

    public function loadDefaultPrivilegesForRole(RoleInterface $role): void
    {
        if (User::ROLE_ANONYMOUS === $role->getRole()) {
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
