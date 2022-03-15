<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\Contributor\Model;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor\Model\Contributor;
use PHPUnit\Framework\TestCase;

final class ContributorTest extends TestCase
{
    /** @test */
    public function itCreatesAContributor(): void
    {
        $contributor = Contributor::fromEmail('foo@foo.bar');

        static::assertInstanceOf(Contributor::class, $contributor);
        static::assertSame('foo@foo.bar', $contributor->email());
    }
}
