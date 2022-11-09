<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\Supplier\Write\Model\Supplier;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateASupplierIdentifierIfItIsNotAValidUuid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The supplier identifier must be a UUID, "%s" given', 'foo'),
            ),
        );

        Identifier::fromString('foo');
    }

    /** @test */
    public function itCreatesASupplierIdentifierIfItsValid(): void
    {
        $uuid = Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(Identifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
