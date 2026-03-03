<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment;

use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserLoader
{
    public function __construct(
        private SimpleFactoryInterface $userFactory,
        private ObjectUpdaterInterface $userUpdater,
        private ValidatorInterface $validator,
        private SaverInterface $userSaver,
        private UserGroupLoader $userGroupLoader,
        private UserRoleLoader $userRoleLoader,
    ) {
    }

    public function createUser(string $username, array $userGroups, array $userRoles): UserInterface
    {
        $user = $this->userFactory->create();
        if (!$user instanceof UserInterface) {
            throw new \LogicException();
        }

        $this->userGroupLoader->createOnlyNecessary($userGroups);
        $this->userRoleLoader->createOnlyNecessary($userRoles);

        $this->userUpdater->update($user, [
            'username' => $username,
            'password' => $username,
            'first_name' => $username,
            'last_name' => $username,
            'email' => $username . '@example.com',
            'groups' => $userGroups,
            'roles' => $userRoles,
        ]);

        $violations = $this->validator->validate($user);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->userSaver->save($user);

        return $user;
    }
}
