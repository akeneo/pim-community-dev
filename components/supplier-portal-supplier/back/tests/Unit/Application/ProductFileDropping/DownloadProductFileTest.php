<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\DownloadProductFile;
use PHPUnit\Framework\TestCase;

final class DownloadProductFileTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheProductFileIdentifier(): void
    {
        $downloadProductFileReflectionClass = new \ReflectionClass(DownloadProductFile::class);
        $properties = $downloadProductFileReflectionClass->getProperties();

        static::assertCount(1, $properties);
        static::assertSame(
            'productFileIdentifier',
            $properties[0]->getName(),
        );
    }
}
