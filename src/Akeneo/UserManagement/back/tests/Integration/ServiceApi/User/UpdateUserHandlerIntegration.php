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

namespace Akeneo\Test\UserManagement\Integration\ServiceApi\User;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\ServiceApi\User\UpdateUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpdateUserHandlerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Akeneo\UserManagement\ServiceApi\ViolationsException;

final class UpdateUserHandlerIntegration extends TestCase
{

    public function testItUpdateUserPassword(): void
    {
        $user = $this->getUserLoader()->createUser('userA', [], ['ROLE_USER']);
        $data = [
            'current_password' => 'userA',
            'new_password' => 'realPassword',
            'new_password_repeat' => 'realPassword',
        ];
        $actualUser = $this->getHandler()->handle(new UpdateUserCommand($user, $data));

        $this->assertFalse($this->get(UserPasswordHasherInterface::class)->isPasswordValid($actualUser, 'userA'));
        $this->assertTrue($this->get(UserPasswordHasherInterface::class)->isPasswordValid($actualUser, 'realPassword'));
    }

    public function testItUpdateUser(): void
    {
        $user = $this->getUserLoader()->createUser('userA', [], ['ROLE_USER']);

        Assert::assertCount(1, $user->getRoles());
        Assert::assertEquals('en_US', $user->getUiLocale()->getCode());
        Assert::assertNull($user->getProperty('proposals_state_notifications'));
        Assert::assertNull($user->getProperty('proposals_to_review_notification'));

        $data = [
            'catalog_default_locale'=> "en_US",
            'user_default_locale' => "fr_FR",
            'roles'=> ["ROLE_USER", "ROLE_CATALOG_MANAGER"],
            'properties' => [
                'proposals_state_notifications' => false,
                'proposals_to_review_notification' => true,
            ]
        ];
        $actualUser = $this->getHandler()->handle(new UpdateUserCommand($user, $data));

        Assert::assertCount(2, $actualUser->getRoles());
        Assert::assertEquals('fr_FR', $user->getUiLocale()->getCode());
        Assert::assertfalse($user->getProperty('proposals_state_notifications'));
        Assert::assertTrue($user->getProperty('proposals_to_review_notification'));
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
        $this->getHandler()->handle(new UpdateUserCommand($user, $data));
    }
    private function getHandler():UpdateUserHandlerInterface {
        return $this->get(UpdateUserHandlerInterface::class);
    }

    private function getUserLoader(): UserLoader {
        return $this->get(UserLoader::class);
    }
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
