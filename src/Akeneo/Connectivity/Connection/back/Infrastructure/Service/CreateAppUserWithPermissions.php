<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateAppUserWithPermissions
{
    private OAuthScopeTransformer $authScopeTransformer;
    private RoleWithPermissionsFactory $roleWithPermissionsFactory;
    private RoleWithPermissionsRepository $roleWithPermissionsRepository;
    private RoleRepository $roleRepository;
    private RoleWithPermissionsSaver $roleWithPermissionsSaver;
    private SaverInterface $userSaver;
    private UserRepository $userRepository;
    private UserFactory $userFactory;

    public function __construct(
        OAuthScopeTransformer $authScopeTransformer,
        RoleWithPermissionsFactory $roleWithPermissionsFactory,
        RoleWithPermissionsRepository $roleWithPermissionsRepository,
        RoleRepository $roleRepository,
        RoleWithPermissionsSaver $roleWithPermissionsSaver,
        SaverInterface $userSaver,
        UserRepository $userRepository,
        UserFactory $userFactory
    ) {
        $this->authScopeTransformer = $authScopeTransformer;
        $this->roleWithPermissionsFactory = $roleWithPermissionsFactory;
        $this->roleWithPermissionsRepository = $roleWithPermissionsRepository;
        $this->roleRepository = $roleRepository;
        $this->roleWithPermissionsSaver = $roleWithPermissionsSaver;
        $this->userSaver = $userSaver;
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
    }

    public function handle(array $scopes): void
    {
        $aclPermissionIds = $this->authScopeTransformer->transform($scopes);

        /**
         * @todo: CREATE USER WITH PERMISSIONS FROM THE SCOPE
         * @see Akeneo\UserManagement\Component\Factory\UserFactory
         * @see Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory
         * -> create a role
         * -> create a user
         * -> create a user group
         * associate this user to this role
         * associate this user to this user group
         */
        $roleCode = 'yell-extenssion-role';
        $role = $this->roleRepository->findOneByIdentifier($roleCode);
        if (null === $role) {
            $roleWithPermissions = $this->roleWithPermissionsFactory->create($aclPermissionIds);
            $roleWithPermissions->role()->setLabel('yell-extenssion-label');
            $roleWithPermissions->role()->setRole('yell-extenssion-role');
            $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);
            $role = $roleWithPermissions->role();
        }
        $appUsername = 'yell-extenssion-username';
        $appUser = $this->userRepository->findOneBy(['username' => $appUsername]);
        if (null === $appUser) {
            $appUser = $this->userFactory->create();
            if (!$appUser instanceof User) {
                throw new \Exception('user factory is not created a user model');
            }
            $appUser->setUsername($appUsername);
            $appUser->setEmail(random_int(99999999, 999999990).'@random.email');
            $appUser->setPassword(random_int(1, 999));
            $appUser->setRoles([$role]);
            $this->userSaver->save($appUser);
        }
    }
}
