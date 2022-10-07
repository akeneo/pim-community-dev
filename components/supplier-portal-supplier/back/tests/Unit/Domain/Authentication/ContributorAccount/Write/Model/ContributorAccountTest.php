<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\Authentication\ContributorAccount\Write\Model;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;

final class ContributorAccountTest extends TestCase
{
    /** @test */
    public function itCreatesAContributorAccount(): void
    {
        $contributorAccount = ContributorAccount::fromEmail('contributor@example.com');
        $this->assertEquals('contributor@example.com', $contributorAccount->email());
        $this->assertNull($contributorAccount->getPassword());
        $this->assertNull($contributorAccount->lastLoggedAt());
        $this->assertIsString($contributorAccount->accessToken());
        $this->assertIsString($contributorAccount->createdAt());
        $this->assertIsString($contributorAccount->accessTokenCreatedAt());
        $this->assertFalse($contributorAccount->hasConsent());
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
            true,
        );
        $this->assertSame('b8b13d0b-496b-4a7c-a574-0d522ba90752', $contributorAccount->identifier());
        $this->assertSame('contributor@example.com', $contributorAccount->email());
        $this->assertSame('P@ssw0rd*', $contributorAccount->getPassword());
        $this->assertSame(
            '1vn466x20fr44wk40w0s88c40c0owwso0sgoksko0kgcggk848',
            $contributorAccount->accessToken(),
        );
        $this->assertSame('2022-06-06 12:52:44', $contributorAccount->accessTokenCreatedAt());
        $this->assertSame('2022-06-06 12:52:44', $contributorAccount->createdAt());
        $this->assertNull($contributorAccount->lastLoggedAt());
        $this->assertTrue($contributorAccount->hasConsent());
    }

    /** @test */
    public function itUpdatesTheContributorAccountPassword(): void
    {
        $contributorAccount = ContributorAccount::fromEmail('contributor@example.com');

        $contributorAccount->setPassword('P@$$w0rdfoo');

        static::assertSame('P@$$w0rdfoo', $contributorAccount->getPassword());
        static::assertNull($contributorAccount->accessToken());
        static::assertNull($contributorAccount->accessTokenCreatedAt());
    }

    /** @test */
    public function itResetsTheContributorAccountPassword(): void
    {
        $contributorAccount = ContributorAccount::hydrate(
            'd52dc837-3122-48cf-aee9-4405dce82600',
            'contributor@example.com',
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'P@$$w0rdfoo',
            'foo',
            (new \DateTimeImmutable())->modify('-2 days')->format('Y-m-d H:i:s'),
            null,
            true,
        );

        $contributorAccount->resetPassword();

        static::assertNull($contributorAccount->getPassword());
        static::assertSame(
            (new \DateTimeImmutable())->format('d'),
            (new \DateTimeImmutable($contributorAccount->accessTokenCreatedAt()))->format('d'),
        );
        static::assertNotSame('foo', $contributorAccount->accessToken());
    }

    /** @test */
    public function itRenewsTheContributorAccountAccessToken(): void
    {
        $contributorAccount = ContributorAccount::hydrate(
            'd52dc837-3122-48cf-aee9-4405dce82600',
            'contributor@example.com',
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'P@$$w0rdfoo',
            'foo',
            (new \DateTimeImmutable())->modify('-2 days')->format('Y-m-d H:i:s'),
            null,
            true,
        );

        $contributorAccount->renewAccessToken();

        static::assertSame(
            (new \DateTimeImmutable())->format('d'),
            (new \DateTimeImmutable($contributorAccount->accessTokenCreatedAt()))->format('d'),
        );
        static::assertNotSame('foo', $contributorAccount->accessToken());
    }
}
