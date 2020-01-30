<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\User\Internal;

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
    /** @var SimpleFactoryInterface */
    private $userFactory;

    /** @var ObjectUpdaterInterface */
    private $userUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $userSaver;

    public function __construct(
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $validator,
        SaverInterface $userSaver
    ) {
        $this->userFactory = $userFactory;
        $this->userUpdater = $userUpdater;
        $this->validator = $validator;
        $this->userSaver = $userSaver;
    }

    public function execute(string $username, string $firstname, string $lastname): User
    {
        $password = $this->generatePassword();
        $username = $this->generateUsername($username);

        $user = $this->userFactory->create();
        $user->defineAsApiUser();
        $this->userUpdater->update(
            $user,
            [
                'username' => $username,
                'password' => $password,
                'first_name' => strtr($firstname, '<>&"', '____'),
                'last_name' => strtr($lastname, '<>&"', '____'),
                'email' => sprintf('%s@example.com', $username),
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

        return new User($user->getId(), $username, $password);
    }

    private function generatePassword(): string
    {
        return str_shuffle(ucfirst(substr(uniqid(), 0, 9)));
    }

    private function generateUsername(string $username): string
    {
        $randomNumberString = str_pad((string) rand(1, 9999), 4, "0", STR_PAD_LEFT);

        return sprintf('%s_%s', $username, $randomNumberString);
    }
}
