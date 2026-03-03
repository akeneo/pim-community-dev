<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Integration\Infrastructure\Cli\EventListener;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Security\SystemUserToken;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Infrastructure\Cli\Registry\AuthenticatedAsAdminCommandRegistry;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AuthenticateCommandAsAdminUserListenerIntegration extends TestCase
{
    private TokenStorageInterface $tokenStorage;

    public function setUp(): void
    {
        parent::setUp();

        $this->tokenStorage = $this->get('security.token_storage');

        $this->authenticateNotSystemUser();
    }

    public function testItAuthenticatesRegisteredCommandAsAdminUser(): void
    {
        $this->assertAuthenticatedUserNameEquals('not_system_user');

        $fakeCommand = new Command('test:authenticate-user-system');
        $event = new ConsoleCommandEvent($fakeCommand, new ArrayInput([]), new ConsoleOutput());

        $this->get(AuthenticatedAsAdminCommandRegistry::class)->registerCommand($fakeCommand->getName());

        $this->get('event_dispatcher')->dispatch($event, 'console.command');

        $this->assertAuthenticatedUserNameEquals(UserInterface::SYSTEM_USER_NAME);
    }

    public function testItDoesNotAuthenticateNotRegisteredCommandAsAdminUser(): void
    {
        $this->assertAuthenticatedUserNameEquals('not_system_user');

        $fakeCommand = new Command('test:whatever');
        $event = new ConsoleCommandEvent($fakeCommand, new ArrayInput([]), new ConsoleOutput());

        $this->get('event_dispatcher')->dispatch($event, 'console.command');

        $this->assertAuthenticatedUserNameEquals('not_system_user');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertAuthenticatedUserNameEquals(string $expectedUserName): void
    {
        $token = $this->tokenStorage->getToken();
        $userName = $token?->getUser()?->getUserIdentifier();

        Assert::assertSame($expectedUserName, $userName);
    }

    private function authenticateNotSystemUser(): void
    {
        $otherUser = $this->get('pim_user.factory.user')->create();
        $otherUser->setUsername('not_system_user');

        $token = new SystemUserToken($otherUser);
        $this->tokenStorage->setToken($token);
    }
}
