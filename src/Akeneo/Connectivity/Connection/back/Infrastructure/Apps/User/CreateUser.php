<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\User;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateUserInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUser implements CreateUserInterface
{
    public function __construct(
        private SimpleFactoryInterface $userFactory,
        private ObjectUpdaterInterface $userUpdater,
        private ValidatorInterface $validator,
        private SaverInterface $userSaver
    ) {
    }

    public function execute(string $username, string $name, array $groups, array $roles, string $appId): int
    {
        $password = $this->generatePassword();

        $userPayload = [
            'username' => $username,
            'password' => $password,
            'first_name' => $name,
            'last_name' => ' ',
            'email' => \sprintf('%s@example.com', $username),
            'groups' => $groups,
            'roles' => $roles,
            'properties' => [
                'app_id' => $appId,
            ],
        ];

        /** @var UserInterface $user */
        $user = $this->userFactory->create();
        $user->defineAsApiUser();
        $this->userUpdater->update($user, $userPayload);

        $errors = $this->validator->validate($user);
        if (0 < \count($errors)) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            throw new \LogicException("The user creation failed :\n" . \implode("\n", $errorMessages));
        }

        $this->userSaver->save($user);

        return $user->getId();
    }

    private function generatePassword(): string
    {
        return \str_shuffle(\ucfirst(\substr(\uniqid(), 0, 9)));
    }
}
