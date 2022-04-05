<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\ValueObject;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Contributor;
use PHPUnit\Framework\TestCase;

class ContributorTest extends TestCase
{
    /** @test */
    public function itCreatesAContributor(): void
    {
        $contributor = Contributor::fromEmail('foo@foo.com');

        static::assertInstanceOf(Contributor::class, $contributor);
        static::assertSame('foo@foo.com', $contributor->email());
    }
}
