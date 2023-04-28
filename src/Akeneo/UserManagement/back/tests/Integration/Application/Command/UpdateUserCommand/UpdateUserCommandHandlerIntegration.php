<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\UserManagement\Integration\Application\Command\UpdateUserCommand;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Application\Command\UpdateUserCommand\UpdateUserCommand;
use Akeneo\UserManagement\Application\Command\UpdateUserCommand\UpdateUserCommandHandler;
use Akeneo\UserManagement\Application\Exception\UserNotFoundException;
use Akeneo\UserManagement\ServiceApi\ViolationsException;
use PHPUnit\Framework\Assert;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UpdateUserCommandHandlerIntegration extends TestCase
{

    public function testItUpdateUserPassword(): void
    {
        $user = $this->getUserLoader()->createUser('userA', [], ['ROLE_USER']);

        $this->assertTrue($this->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'userA'));
        $this->assertFalse($this->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'realPassword'));

        $data = [
            'current_password' => 'userA',
            'new_password' => 'realPassword',
            'new_password_repeat' => 'realPassword',
        ];

        $actualUser = $this->getHandler()->handle(new UpdateUserCommand($user->getId(), $data));

        $this->assertFalse($this->get(UserPasswordHasherInterface::class)->isPasswordValid($actualUser, 'userA'));
        $this->assertTrue($this->get(UserPasswordHasherInterface::class)->isPasswordValid($actualUser, 'realPassword'));
    }

    public function testItUpdateUser(): void
    {
        $user = $this->getUserLoader()->createUser('userA', [], ['ROLE_USER']);

        Assert::assertCount(1, $user->getRoles());
        Assert::assertEquals('en_US', $user->getUiLocale()->getCode());
        Assert::assertEquals('userA@example.com', $user->getEmail());

        $data = [
            'email' => 'user@test.fr',
            'catalog_default_locale'=> "en_US",
            'user_default_locale' => "fr_FR",
            'roles'=> ["ROLE_USER", "ROLE_CATALOG_MANAGER"],
        ];
        $actualUser = $this->getHandler()->handle(new UpdateUserCommand($user->getId(), $data));

        Assert::assertCount(2, $actualUser->getRoles());
        Assert::assertEquals('fr_FR', $user->getUiLocale()->getCode());
        Assert::assertEquals('user@test.fr', $user->getEmail());
    }

    public function testItThrowsNotFoundErrors(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Username with id "-1" not found');
        $data = [
            'current_password' => 'userFakeA',
            'new_password' => '1234',
            'new_password_repeat' => '12345',
        ];
        $this->getHandler()->handle(new UpdateUserCommand(-1, $data));
    }

    public function testItThrowsValidationErrors(): void
    {
        $this->expectException(ViolationsException::class);
        $this->expectExceptionMessage('Wrong password');
        $this->expectExceptionMessage('Password must contain at least 8 characters');
        $this->expectExceptionMessage('Passwords do not match');
        $user = $this->getUserLoader()->createUser('userA', [], ['ROLE_USER']);
        $data = [
            'current_password' => 'userFakeA',
            'new_password' => '1234',
            'new_password_repeat' => '12345',
        ];
        $this->getHandler()->handle(new UpdateUserCommand($user->getId(), $data));
    }
    private function getHandler():UpdateUserCommandHandler {
        return $this->get(UpdateUserCommandHandler::class);
    }

    private function getUserLoader(): UserLoader {
        return $this->get(UserLoader::class);
    }
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
