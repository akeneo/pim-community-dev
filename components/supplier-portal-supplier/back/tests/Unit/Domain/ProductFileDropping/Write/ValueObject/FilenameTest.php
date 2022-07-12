<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Write\ValueObject;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use PHPUnit\Framework\TestCase;

final class FilenameTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateAFilenameIfItsEmpty(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The filename cannot be empty.'));

        Filename::fromString('');
    }

    /** @test */
    public function itCreatesAndGetsAFilenameIfItsValid(): void
    {
        $filename = Filename::fromString('supplier-file.xlsx');

        static::assertInstanceOf(Filename::class, $filename);
        static::assertSame('supplier-file.xlsx', (string) $filename);
    }
}
