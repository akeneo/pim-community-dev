<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Test\Integration\Loader;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

final class FixturesUserHelper
{
    public function __construct(
        private UserFactory $userFactory,
        private SaverInterface $userSaver,
        private RoleRepositoryInterface $roleRepository,
        private RoleWithPermissionsFactory $roleWithPermissionFactory,
        private RoleWithPermissionsSaver $roleWithPermissionsSaver,
        private AclManager $aclManager,
        private LocaleRepositoryInterface $localeRepository,
    ) {
    }

    public function createUser(string $username, array $roleNames, string $userType = 'user'): void
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $user->setId(hexdec(uniqid()));
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');
        $user->setUILocale($this->localeRepository->findOneByIdentifier('en_US'));
        $user->setCatalogLocale($this->localeRepository->findOneByIdentifier('en_US'));

        switch ($userType) {
            case 'api':
                $user->defineAsApiUser();
                break;
            case 'job':
                $user->defineAsJobUser();
                break;
        }

        foreach ($roleNames as $roleName) {
            $role = $this->roleRepository->findOneByIdentifier($roleName);

            $user->addRole($role);
        }

        $this->userSaver->save($user);
    }

    public function createJobUser(string $username, array $roleNames): void
    {
        $this->createUser($username, $roleNames, 'job');
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
