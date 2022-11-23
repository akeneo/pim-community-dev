<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\Authentication\ContributorAccount\Read\Model;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;
use Akeneo\SupplierPortal\Supplier\Test\Unit\Fakes\FrozenClock;
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
            (new FrozenClock('2022-09-07 08:54:38'))->now(),
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

    /** @test */
    public function itTellsIfTheAccessTokenIsValid(): void
    {
        $sut = new ContributorAccount(
            '9f4c017c-7682-4f83-9099-dd9afcada1a2',
            'burger@example.com',
            'foo',
            (new FrozenClock('2022-09-07 08:54:38'))->now(),
        );

        $now = (new FrozenClock('2022-09-12 08:12:00'))->now();
        static::assertTrue($sut->isAccessTokenValid($now));
    }

    /** @test */
    public function itTellsIfTheTheAccessTokenIsNotValidAnymore(): void
    {
        $sut = new ContributorAccount(
            '9f4c017c-7682-4f83-9099-dd9afcada1a2',
            'burger@example.com',
            'foo',
            (new FrozenClock('2022-08-28 08:54:38'))->now(),
        );

        $now = (new FrozenClock('2022-09-12 08:12:00'))->now();
        static::assertFalse($sut->isAccessTokenValid($now));
    }
}
