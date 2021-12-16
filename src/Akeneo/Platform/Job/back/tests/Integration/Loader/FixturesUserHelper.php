<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Loader;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

final class FixturesUserHelper
{
    private UserFactory $userFactory;
    private SaverInterface $userSaver;
    private RoleRepositoryInterface $roleRepository;
    private RoleWithPermissionsFactory $roleWithPermissionFactory;
    private RoleWithPermissionsSaver $roleWithPermissionsSaver;
    private AclManager $aclManager;
    private LocaleRepositoryInterface $localeRepository;

    public function __construct(
        UserFactory $userFactory,
        SaverInterface $userSaver,
        RoleRepositoryInterface $roleRepository,
        RoleWithPermissionsFactory $roleWithPermissionFactory,
        RoleWithPermissionsSaver $roleWithPermissionsSaver,
        AclManager $aclManager,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->userFactory = $userFactory;
        $this->userSaver = $userSaver;
        $this->roleRepository = $roleRepository;
        $this->roleWithPermissionFactory = $roleWithPermissionFactory;
        $this->roleWithPermissionsSaver = $roleWithPermissionsSaver;
        $this->aclManager = $aclManager;
        $this->localeRepository = $localeRepository;
    }

    public function createUser(string $username, array $roleNames)
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $user->setId(hexdec(uniqid()));
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');
        $user->setUILocale($this->localeRepository->findOneByIdentifier('en_US'));
        $user->setCatalogLocale($this->localeRepository->findOneByIdentifier('en_US'));

        foreach ($roleNames as $roleName) {
            $role = $this->roleRepository->findOneByIdentifier($roleName);

            $user->addRole($role);
        }

        $this->userSaver->save($user);
    }

    public function createRole(string $roleName, array $acls): void
    {
        $roleWithPermission = $this->roleWithPermissionFactory->create();
        $roleWithPermission->role()->setRole($roleName);
        $roleWithPermission->role()->setLabel($roleName);

        $permissions = [];
        foreach ($acls as $acl) {
            $permissions[sprintf('action:%s', $acl)] = true;
        }

        $roleWithPermission->setPermissions($permissions);
        $this->roleWithPermissionsSaver->saveAll([$roleWithPermission]);

        $this->aclManager->flush();
        $this->aclManager->clearCache();
    }
}
