<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\Contributor\ValueObject;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateAContributorIdentifierIfItIsNotAValidUuid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The contributor identifier must be a UUID, "%s" given', 'foo')
            )
        );

        Contributor\ValueObject\Identifier::fromString('foo');
    }

    /** @test */
    public function itCreatesAContributorIdentifierIfItsValid(): void
    {
        $uuid = Contributor\ValueObject\Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(Contributor\ValueObject\Identifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
