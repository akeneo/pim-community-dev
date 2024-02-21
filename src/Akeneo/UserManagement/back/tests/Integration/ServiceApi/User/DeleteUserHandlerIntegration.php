<?php

declare(strict_types=1);


/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\UserManagement\Integration\ServiceApi\User;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserCommand;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserHandlerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class DeleteUserHandlerIntegration extends TestCase
{
    public function testItDeleteAUser(): void
    {
        $username = 'a_user';

        $this->createUser($username);
        $this->assertUserExist($username);
        $this->deleteUser($username);
        $this->assertUserDoesNotExist($username);
    }

    public function testItThrowsAnExceptionUserDoesNotExist(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->deleteUser('another_user');
    }

    private function deleteUser(string $username): void
    {
        $this->getHandler()->handle(
            new DeleteUserCommand($username)
        );
    }

    private function createUser(string $username): void
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', $username));
        $user->setPassword('fake');

        $this->get('pim_user.saver.user')->save($user);
    }

    private function assertUserExist(string $username): void
    {
        $this->assertNotNull($this->getUser($username));
    }

    private function assertUserDoesNotExist(string $username): void
    {
        $this->assertNull($this->getUser($username));
    }

    private function getUser(string $username): ?UserInterface
    {
        $repository = $this->get('pim_user.repository.user');

        return $repository->findOneBy(['username' => $username]);
    }

    private function getHandler(): DeleteUserHandlerInterface
    {
        return $this->get(DeleteUserHandlerInterface::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
