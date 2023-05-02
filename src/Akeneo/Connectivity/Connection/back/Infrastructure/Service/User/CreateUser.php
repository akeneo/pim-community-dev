<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUser implements CreateUserInterface
{
    public function __construct(private SimpleFactoryInterface $userFactory, private ObjectUpdaterInterface $userUpdater, private ValidatorInterface $validator, private SaverInterface $userSaver)
    {
    }

    /**
     * @inheritdoc
     */
    public function execute(string $username, string $firstname, string $lastname, ?array $groups = null, ?array $roles = null): User
    {
        $password = $this->generatePassword();
        $username = $this->generateUsername($username);

        $userPayload = [
            'username' => $username,
            'password' => $password,
            'first_name' => \strtr($firstname, '<>&"', '____'),
            'last_name' => \strtr($lastname, '<>&"', '____'),
            'email' => \sprintf('%s@example.com', $username),
        ];

        if (null !== $groups) {
            $userPayload['groups'] = $groups;
        }

        if (null !== $roles) {
            $userPayload['roles'] = $roles;
        }

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

        return new User($user->getId(), $username, $password);
    }

    private function generatePassword(): string
    {
        return \str_shuffle(\ucfirst(\substr(\uniqid(), 0, 9)));
    }

    private function generateUsername(string $username): string
    {
        $randomNumberString = \str_pad((string) \random_int(1, 9999), 4, "0", STR_PAD_LEFT);

        return \sprintf('%s_%s', $username, $randomNumberString);
    }
}
