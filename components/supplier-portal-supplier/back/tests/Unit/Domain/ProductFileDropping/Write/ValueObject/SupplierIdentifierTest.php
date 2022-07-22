<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Write\ValueObject;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\SupplierIdentifier;
use PHPUnit\Framework\TestCase;

final class SupplierIdentifierTest extends TestCase
{
    /** @test */
    public function itThrowsAnErrorIfItIsNotAValidUuid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The supplier identifier must be a UUID, "%s" given', 'foo'),
            ),
        );

        SupplierIdentifier::fromString('foo');
    }

    /** @test */
    public function itCreatesASupplierIdentifierIfItsValid(): void
    {
        $uuid = SupplierIdentifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(SupplierIdentifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
