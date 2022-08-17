<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFileNameAndResourceFile;
use PHPUnit\Framework\TestCase;

final class SupplierFileNameAndResourceFileTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheFilenameAndTheResourceFile(): void
    {
        $productFileNameAndResourceFileReflectionClass = new \ReflectionClass(
            SupplierFileNameAndResourceFile::class,
        );
        $properties = $productFileNameAndResourceFileReflectionClass->getProperties();
        $fakeResource = new \stdClass();
        $productFileNameAndResourceFile = new SupplierFileNameAndResourceFile('file.xlsx', $fakeResource);

        static::assertCount(2, $properties);
        static::assertSame('file.xlsx', $productFileNameAndResourceFile->originalFilename);
        static::assertSame($fakeResource, $productFileNameAndResourceFile->file);
    }
}
