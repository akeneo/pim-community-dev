<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Domain\Authentication\ContributorAccount\Read\Model;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Read\Model\ContributorAccount;
use PHPUnit\Framework\TestCase;

final class ContributorAccountUnit extends TestCase
{
    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new ContributorAccount('9f4c017c-7682-4f83-9099-dd9afcada1a2', 'foo', true);

        static::assertSame(
            ['id' => '9f4c017c-7682-4f83-9099-dd9afcada1a2', 'accessToken' => 'foo', 'isAccessTokenValid' => true],
            $sut->toArray(),
        );
    }
}
