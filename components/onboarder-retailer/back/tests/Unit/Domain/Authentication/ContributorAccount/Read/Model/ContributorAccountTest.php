<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Domain\Authentication\ContributorAccount\Read\Model;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;

final class ContributorAccountTest extends TestCase
{
    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new ContributorAccount('9f4c017c-7682-4f83-9099-dd9afcada1a2', 'burger@example.com', 'foo', true);

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
}
