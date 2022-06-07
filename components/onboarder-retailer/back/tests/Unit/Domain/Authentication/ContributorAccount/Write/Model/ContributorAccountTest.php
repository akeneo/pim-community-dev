<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Domain\Authentication\ContributorAccount\Write\Model;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;

final class ContributorAccountTest extends TestCase
{
    /** @test */
    public function itCreatesAContributorAccount(): void
    {
        $contributorAccount = ContributorAccount::fromEmail('contributor@example.com');
        $this->assertEquals('contributor@example.com', $contributorAccount->email());
        $this->assertNull($contributorAccount->password());
        $this->assertNull($contributorAccount->lastLoggedAt());
        $this->assertIsString($contributorAccount->accessToken());
        $this->assertIsString($contributorAccount->createdAt());
        $this->assertIsString($contributorAccount->accessTokenCreatedAt());
    }

    /** @test */
    public function itCanBeHydrated(): void
    {
        $contributorAccount = ContributorAccount::hydrate(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'contributor@example.com',
            '2022-06-06 12:52:44',
            'P@ssw0rd*',
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            '2022-06-06 12:52:44',
            null,
        );
        $this->assertSame('b8b13d0b-496b-4a7c-a574-0d522ba90752', $contributorAccount->identifier());
        $this->assertSame('contributor@example.com', $contributorAccount->email());
        $this->assertSame('P@ssw0rd*', $contributorAccount->password());
        $this->assertSame(
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            $contributorAccount->accessToken(),
        );
        $this->assertSame('2022-06-06 12:52:44', $contributorAccount->accessTokenCreatedAt());
        $this->assertSame('2022-06-06 12:52:44', $contributorAccount->createdAt());
        $this->assertNull($contributorAccount->lastLoggedAt());
    }

    /** @test */
    public function itUpdatesTheContributorAccountPassword(): void
    {
        $contributorAccount = ContributorAccount::fromEmail('contributor@example.com');

        $contributorAccount->setPassword('P@$$w0rdfoo');

        static::assertSame('P@$$w0rdfoo', $contributorAccount->password());
        static::assertNull($contributorAccount->accessToken());
        static::assertNull($contributorAccount->accessTokenCreatedAt());
    }
}
