<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Model\ProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\SupplierIdentifier;
use PHPUnit\Framework\TestCase;

final class ProductIdentifierTest extends TestCase
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
    public function itCreatesAProductIdentifierIfItsValid(): void
    {
        $uuid = SupplierIdentifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(SupplierIdentifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
