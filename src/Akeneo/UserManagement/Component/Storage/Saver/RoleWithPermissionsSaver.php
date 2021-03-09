<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Storage\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsSaver implements BulkSaverInterface
{
    private ObjectManager $objectManager;
    private EventDispatcherInterface $eventDispatcher;
    private AclManager $aclManager;

    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        AclManager $aclManager
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->aclManager = $aclManager;
    }

    public function saveAll(array $rolesWithPermissions, array $options = []): void
    {
        if (empty($rolesWithPermissions)) {
            return;
        }
        Assert::allIsInstanceOf($rolesWithPermissions, RoleWithPermissions::class);

        $options['unitary'] = false;
        $userRoles = \array_map(
            fn (RoleWithPermissions $roleWithPermissions): RoleInterface => $roleWithPermissions->role(),
            $rolesWithPermissions
        );
        $this->eventDispatcher->dispatch(new GenericEvent($userRoles, $options), StorageEvents::PRE_SAVE_ALL);

        $areObjectsNew = array_map(
            function (RoleInterface $role) {
                return null === $role->getId();
            },
            $userRoles
        );

        foreach ($userRoles as $i => $role) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($role, array_merge($options, ['is_new' => $areObjectsNew[$i]])),
                StorageEvents::PRE_SAVE
            );

            $this->objectManager->persist($role);
        }
        $this->objectManager->flush();

        foreach ($rolesWithPermissions as $i => $roleWithPermissions) {
            $this->eventDispatcher->dispatch(
                new GenericEvent($roleWithPermissions->role(), array_merge($options, ['is_new' => $areObjectsNew[$i]])),
                StorageEvents::POST_SAVE
            );
        }

        $this->eventDispatcher->dispatch(new GenericEvent($userRoles, $options), StorageEvents::POST_SAVE_ALL);

        foreach ($rolesWithPermissions as $roleWithPermissions) {
            $this->updatePermissions($roleWithPermissions);
        }
    }

    private function updatePermissions(RoleWithPermissions $roleWithPermissions): void
    {
        if (User::ROLE_ANONYMOUS === $roleWithPermissions->role()->getRole()) {
            return;
        }
        $sid = $this->aclManager->getSid($roleWithPermissions->role());
        $privilegeRepository = $this->aclManager->getPrivilegeRepository();
        $privileges = $privilegeRepository->getPrivileges($sid);
        $privilegeIds = $roleWithPermissions->permissions();

        foreach ($privileges as $privilege) {
            $isGranted = $privilegeIds[$privilege->getIdentity()->getId()] ?? null;
            if (null === $isGranted) {
                continue;
            }
            foreach ($privilege->getPermissions() as $permission) {
                $permission->setAccessLevel($isGranted ? AccessLevel::SYSTEM_LEVEL : AccessLevel::NONE_LEVEL);
            }
        }

        $privilegeRepository->savePrivileges($sid, $privileges);
    }
}
