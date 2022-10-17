<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Read;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFile;
use PHPUnit\Framework\TestCase;

final class DownloadProductFileTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheProductFileIdentifierAndTheContributorEmail(): void
    {
        $downloadProductFileReflectionClass = new \ReflectionClass(DownloadProductFile::class);
        $properties = $downloadProductFileReflectionClass->getProperties();

        $sut = new DownloadProductFile('9c89942b-4be9-463b-90d8-69c9f000500c');

        static::assertCount(1, $properties);
        static::assertSame('9c89942b-4be9-463b-90d8-69c9f000500c', $sut->productFileIdentifier);
    }
}
