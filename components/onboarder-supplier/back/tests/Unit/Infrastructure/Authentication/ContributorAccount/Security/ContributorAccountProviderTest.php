<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Infrastructure\Authentication\ContributorAccount\Security;

use Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Query\Sql\DatabaseGetContributorAccountByEmail;
use Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccountProvider;
use PHPUnit\Framework\TestCase;

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
        $query->expects($this->exactly(2))->method('__invoke')->with('burger@example.com')->willReturn($contributorAccount);
        $provider = new ContributorAccountProvider($query);

        $this->assertEquals($contributorAccount, $provider->loadUserByIdentifier('burger@example.com'));
        $this->assertEquals($contributorAccount, $provider->loadUserByUsername('burger@example.com'));
    }
}
