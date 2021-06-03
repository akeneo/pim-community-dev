<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    private ObjectUpdaterInterface $userUpdater;
    private ValidatorInterface $validator;

    public function __construct(
        OAuthScopeTransformer $authScopeTransformer,
        RoleWithPermissionsFactory $roleWithPermissionsFactory,
        RoleWithPermissionsRepository $roleWithPermissionsRepository,
        RoleRepository $roleRepository,
        RoleWithPermissionsSaver $roleWithPermissionsSaver,
        SaverInterface $userSaver,
        UserRepository $userRepository,
        UserFactory $userFactory,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $validator
    ) {
        $this->authScopeTransformer = $authScopeTransformer;
        $this->roleWithPermissionsFactory = $roleWithPermissionsFactory;
        $this->roleWithPermissionsRepository = $roleWithPermissionsRepository;
        $this->roleRepository = $roleRepository;
        $this->roleWithPermissionsSaver = $roleWithPermissionsSaver;
        $this->userSaver = $userSaver;
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->validator = $validator;
    }

    public function handle(Client $client, array $scopes): User
    {
        $aclPermissionIds = $this->authScopeTransformer->transform($scopes);

        /**
         * @todo:
         * -> create a user group
         * associate this user to this user group
         */
        $roleCode = sprintf('%s-role', $this->slugify($client->getLabel()));

        $role = $this->roleRepository->findOneByIdentifier($roleCode);
        if (null === $role) {
            $roleWithPermissions = $this->roleWithPermissionsFactory->create($aclPermissionIds);
            $roleWithPermissions->role()->setLabel($roleCode);
            $roleWithPermissions->role()->setRole($roleCode);
            /**
             * TODO
             * ^ Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException {#3494 ▼
            #propertyValue: Akeneo\UserManagement\Component\Model\Role {#3378 ▶}
            #className: "Akeneo\UserManagement\Component\Updater\UserUpdater"
            #propertyName: "roles"
            #message: "Property "roles" expects a valid role. The role does not exist, "yell-extenssion-role" given."
            #code: 300
            #file: "/srv/pim/src/Akeneo/Tool/Component/StorageUtils/Exception/InvalidPropertyException.php"
            #line: 106
             */
            $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);
            $role = $roleWithPermissions->role();
        }

        return $this->createUser($client->getLabel(), [$role]);
    }

    private function createUser(string $username, array $roles): User
    {
        $user = $this->userFactory->create();
        $user->defineAsApiUser();
        $this->userUpdater->update(
            $user,
            [
                'username' => $username,
                'password' => $this->generatePassword(),
                'first_name' => $this->slugify($username),
                'last_name' => $this->slugify($username),
                'email' => sprintf('%s@example.com', $username),
                'roles' => $roles
            ]
        );

        $errors = $this->validator->validate($user);
        if (0 < count($errors)) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new \LogicException("The user creation failed :\n" . implode("\n", $errorMessages));
        }

        $this->userSaver->save($user);

        return $user;
    }

    private function slugify(string $string): string
    {
        return strtr($string, '<>&" ', '_____');
    }

    private function generatePassword(): string
    {
        return str_shuffle(ucfirst(substr(uniqid(), 0, 9)));
    }
}
