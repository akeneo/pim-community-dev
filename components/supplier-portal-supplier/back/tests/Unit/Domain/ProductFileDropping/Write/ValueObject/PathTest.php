<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Write\ValueObject;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateAPathIfItsEmpty(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The path cannot be empty.'));

        Path::fromString(' ');
    }

    /** @test */
    public function itCreatesAndGetsAPathIfItsValid(): void
    {
        $path = Path::fromString('2/f/a/4/2fa4afe5465afe5655supplier-file.xlsx');

        static::assertInstanceOf(Path::class, $path);
        static::assertSame('2/f/a/4/2fa4afe5465afe5655supplier-file.xlsx', (string) $path);
    }
}
