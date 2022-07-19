<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Write\ValueObject;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\ContributorIdentifier;
use PHPUnit\Framework\TestCase;

final class ContributorIdentifierTest extends TestCase
{
    /** @test */
    public function itThrowsAnErrorIfItIsNotAValidUuid(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The contributor identifier must be a UUID, "%s" given', 'foo'),
            ),
        );

        ContributorIdentifier::fromString('foo');
    }

    /** @test */
    public function itCreatesAContributorIdentifierIfItsValid(): void
    {
        $uuid = ContributorIdentifier::fromString('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(ContributorIdentifier::class, $uuid);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', (string) $uuid);
    }
}
