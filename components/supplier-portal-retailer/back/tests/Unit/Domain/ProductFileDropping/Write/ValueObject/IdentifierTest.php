<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\ValueObject;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    /** @test */
    public function itThrowsAnErrorIfItIsNotAValidUuid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The product file identifier must be a UUID, "%s" given', 'foo'),
            ),
        );

        Identifier::fromString('foo');
    }

    /** @test */
    public function itCreatesAProductFileIdentifierIfItsValid(): void
    {
        $uuid = Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(Identifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
