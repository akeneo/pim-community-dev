<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserCommand;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserHandlerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class DeleteUserHandler implements DeleteUserHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserManager $userManager,
    ) {
    }

    public function handle(DeleteUserCommand $deleteUserCommand): void
    {
        /** @var UserInterface|null $user */
        $user = $this->userRepository->findOneBy(['username' => $deleteUserCommand->username]);

        if (null === $user) {
            throw new UserNotFoundException();
        }

        $this->userManager->deleteUser($user);
    }
}
