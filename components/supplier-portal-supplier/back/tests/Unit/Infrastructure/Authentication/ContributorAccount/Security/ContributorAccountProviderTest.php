<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Infrastructure\Authentication\ContributorAccount\Security;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql\DatabaseGetContributorAccountByEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccountProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class ContributorAccountProviderTest extends TestCase
{
    /** @test */
    public function itSupportsOnlyContributorAccountUser(): void
    {
        $provider = new ContributorAccountProvider($this->createMock(DatabaseGetContributorAccountByEmail::class));
        $this->assertTrue($provider->supportsClass(ContributorAccount::class));
        $this->assertFalse($provider->supportsClass(\stdClass::class));
    }

    /** @test */
    public function itCanLoadAUserFromItsEmail(): void
    {
        $contributorAccount = new ContributorAccount('burger@example.com', 'password');

        $query = $this->createMock(DatabaseGetContributorAccountByEmail::class);
        $query
            ->expects($this->exactly(1))
            ->method('__invoke')
            ->with('burger@example.com')
            ->willReturn($contributorAccount);

        $provider = new ContributorAccountProvider($query);

        $this->assertEquals($contributorAccount, $provider->loadUserByIdentifier('burger@example.com'));
    }

    /** @test */
    public function itThrowsAnExceptionWhenLoadingANonExistingUser(): void
    {
        $query = $this->createMock(DatabaseGetContributorAccountByEmail::class);
        $query
            ->expects($this->exactly(1))
            ->method('__invoke')
            ->with('burger@example.com')
            ->willReturn(null);

        $provider = new ContributorAccountProvider($query);

        $this->expectException(UserNotFoundException::class);

        $provider->loadUserByIdentifier('burger@example.com');
    }

    /** @test */
    public function itThrowsAnExceptionWhenRefreshingANonExistingUser(): void
    {
        $query = $this->createMock(DatabaseGetContributorAccountByEmail::class);
        $query
            ->expects($this->exactly(1))
            ->method('__invoke')
            ->with('burger@example.com')
            ->willReturn(null);

        $provider = new ContributorAccountProvider($query);

        $this->expectException(UserNotFoundException::class);

        $provider->refreshUser(new ContributorAccount('burger@example.com', 'password'));
    }
}
