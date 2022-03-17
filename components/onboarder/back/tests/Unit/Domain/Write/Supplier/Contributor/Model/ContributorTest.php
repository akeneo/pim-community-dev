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
        $contributor = Contributor::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'foo@foo.bar',
            '44ce8069-8da1-4986-872f-311737f46f01',
        );

        static::assertInstanceOf(Contributor::class, $contributor);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $contributor->identifier());
        static::assertSame('foo@foo.bar', $contributor->email());
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f01', $contributor->supplierIdentifier());
    }
}
