<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;
use PHPUnit\Framework\TestCase;

final class ProductFileNameAndResourceFileTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheFilenameAndTheResourceFile(): void
    {
        $productFileNameAndResourceFileReflectionClass = new \ReflectionClass(
            ProductFileNameAndResourceFile::class,
        );
        $properties = $productFileNameAndResourceFileReflectionClass->getProperties();
        $fakeResource = new \stdClass();
        $productFileNameAndResourceFile = new ProductFileNameAndResourceFile('file.xlsx', $fakeResource);

        static::assertCount(2, $properties);
        static::assertSame('file.xlsx', $productFileNameAndResourceFile->originalFilename);
        static::assertSame($fakeResource, $productFileNameAndResourceFile->file);
    }
}
