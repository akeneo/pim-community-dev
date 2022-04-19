<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\ValueObject;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Contributors;
use PHPUnit\Framework\TestCase;

class ContributorsTest extends TestCase
{
    /** @test */
    public function itCreatesACollectionOfContributors(): void
    {
        $contributors = Contributors::fromEmails(['zoo@zoo.com', 'moo@moo.com']);

        static::assertInstanceOf(Contributors::class, $contributors);
        static::assertSame([
            ['email' => 'zoo@zoo.com'],
            ['email' => 'moo@moo.com'],
        ], $contributors->toArray());
        static::assertCount(2, $contributors);
    }
}
