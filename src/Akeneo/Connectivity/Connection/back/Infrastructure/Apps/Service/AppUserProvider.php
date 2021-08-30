<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Service\AppUserProviderInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\AppRoleWithScopesFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppUserProvider implements AppUserProviderInterface
{
    private UserManager $userManager;
    private SimpleFactoryInterface $userFactory;
    private ObjectUpdaterInterface $userUpdater;
    private SaverInterface $userSaver;
    private SimpleFactoryInterface $userGroupFactory;
    private ObjectUpdaterInterface $userGroupUpdater;
    private SaverInterface $userGroupSaver;
    private AppRoleWithScopesFactory $roleFactory;
    private ValidatorInterface $validator;

    public function __construct(
        UserManager $userManager,
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        SaverInterface $userSaver,
        SimpleFactoryInterface $userGroupFactory,
        ObjectUpdaterInterface $userGroupUpdater,
        SaverInterface $userGroupSaver,
        AppRoleWithScopesFactory $roleFactory,
        ValidatorInterface  $validator
    ) {
        $this->userManager = $userManager;
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->userSaver = $userSaver;
        $this->userGroupFactory = $userGroupFactory;
        $this->userGroupUpdater = $userGroupUpdater;
        $this->userGroupSaver = $userGroupSaver;
        $this->roleFactory = $roleFactory;
        $this->validator = $validator;
    }

    public function createUser(string $appName, array $scopes): UserInterface
    {
        $username = $this->slugify($appName);
        $password = $this->generatePassword();

        $existingUser = $this->userManager->findUserByUsername($username);

        if (null !== $existingUser) {
            return $existingUser;
        }

        $role = $this->roleFactory->createRole($username, $scopes);
        $group = $this->createAppUserGroup($username);

        /** @var User $user */
        $user = $this->userFactory->create();
        $user->defineAsApiUser();
        $this->userUpdater->update(
            $user,
            [
                'username' => $username,
                'password' => $password,
                'first_name' => $username,
                'last_name' => $username,
                'email' => sprintf('%s@example.com', $username),
                'roles' => [$role->getRole()],
                'groups' => [$group->getName()]
            ]
        );

        $this->validate($user);

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

    private function validate(object $object): void
    {
        $errors = $this->validator->validate($object);
        if (0 < count($errors)) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new \RuntimeException("The object creation failed :\n" . implode("\n", $errorMessages));
        }
    }

    private function createAppUserGroup(string $groupName): GroupInterface
    {
        $group = $this->userGroupFactory->create();

        $this->userGroupUpdater->update($group, ['name' => $groupName]);
        $this->validate($group);
        $this->userGroupSaver->save($group);

        return $group;
    }
}
