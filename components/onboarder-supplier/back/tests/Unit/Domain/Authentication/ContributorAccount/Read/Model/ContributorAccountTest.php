<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Domain\Authentication\ContributorAccount\Read\Model;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;

final class ContributorAccountTest extends TestCase
{
    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new ContributorAccount(
            '9f4c017c-7682-4f83-9099-dd9afcada1a2',
            'burger@example.com',
            'foo',
            new \DateTimeImmutable(),
        );

        static::assertSame(
            [
                'id' => '9f4c017c-7682-4f83-9099-dd9afcada1a2',
                'email' => 'burger@example.com',
                'accessToken' => 'foo',
                'isAccessTokenValid' => true,
            ],
            $sut->toArray(),
        );
    }

    public function itDoesTellThatTheAccessTokenIsValid(): void
    {
        $sut = new ContributorAccount(
            '9f4c017c-7682-4f83-9099-dd9afcada1a2',
            'burger@example.com',
            'foo',
            new \DateTimeImmutable(),
        );

        static::assertTrue($sut->isAccessTokenValid($sut->accessTokenCreatedAt));
    }

    public function itDoesTellThatTheTheAccessTokenIsNotValidAnymore(): void
    {
        $expiredAccessToken = (new \DateTimeImmutable())->modify('- 15 days');
        $sut = new ContributorAccount(
            '9f4c017c-7682-4f83-9099-dd9afcada1a2',
            'burger@example.com',
            'foo',
            $expiredAccessToken,
        );

        static::assertFalse($sut->isAccessTokenValid($sut->accessTokenCreatedAt));
    }
}
