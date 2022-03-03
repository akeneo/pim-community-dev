<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Identifier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class IdentifierTest extends KernelTestCase
{
    /** @test */
    public function itDoesNotCreateASupplierIdentifierIfItsEmpty(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The supplier identifier cannot be empty.'));

        Identifier::fromString('');
    }

    /** @test */
    public function itDoesNotCreateASupplierIdentifierIfItIsNotAValidUuid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The supplier identifier must be a UUID, "%s" given', 'foo')
            )
        );

        Identifier::fromString('foo');
    }

    /** @test */
    public function itCreatesAndGetsASupplierIdentifierIfItsValid(): void
    {
        $uuid = Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(Identifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
