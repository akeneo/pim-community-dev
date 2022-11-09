<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileImport\Write\Model\ProductFileImport;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport\ProductFileIdentifier;
use PHPUnit\Framework\TestCase;

final class ProductFileIdentifierTest extends TestCase
{
    /** @test */
    public function itThrowsAnErrorIfItIsNotAValidUuid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The product file identifier must be a UUID, "%s" given', 'foo'),
            ),
        );

        ProductFileIdentifier::fromString('foo');
    }

    /** @test */
    public function itCreatesAProductFileIdentifierIfItsValid(): void
    {
        $uuid = ProductFileIdentifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(ProductFileIdentifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
