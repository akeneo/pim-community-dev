<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileDownloadError;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetSupplierFilePath;
use Akeneo\SupplierPortal\Retailer\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class DownloadProductFileHandlerTest extends TestCase
{
    /** @test */
    public function itDownloadsAProductFile(): void
    {
        $getSupplierFilePathMock = $this->createMock(GetSupplierFilePath::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $eventDispatcher = new StubEventDispatcher();
        $sut = new DownloadProductFileHandler($getSupplierFilePathMock, $downloadStoredProductFileMock, $eventDispatcher, new NullLogger());

        $getSupplierFilePathMock->expects($this->once())->method('__invoke')->willReturn('path/to/file.xlsx');
        $fakeResource = new \stdClass();
        $downloadStoredProductFileMock->expects($this->once())->method('__invoke')->with('path/to/file.xlsx')->willReturn($fakeResource);

        $this->assertSame($fakeResource, ($sut)(new DownloadProductFile('file-identifier')));
        $this->assertEquals([new ProductFileDownloaded('file-identifier')], $eventDispatcher->getDispatchedEvents());
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileDoesNotExistInTheDatabase(): void
    {
        $getSupplierFilePathMock = $this->createMock(GetSupplierFilePath::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $sut = new DownloadProductFileHandler($getSupplierFilePathMock, $downloadStoredProductFileMock, new StubEventDispatcher(), new NullLogger());

        $getSupplierFilePathMock->expects($this->once())->method('__invoke')->willReturn(null);

        $this->expectException(SupplierFileDoesNotExist::class);
        ($sut)(new DownloadProductFile('file-identifier'));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileCouldNotBeRetrieved(): void
    {
        $getSupplierFilePathMock = $this->createMock(GetSupplierFilePath::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $eventDispatcher = new StubEventDispatcher();
        $sut = new DownloadProductFileHandler($getSupplierFilePathMock, $downloadStoredProductFileMock, $eventDispatcher, new NullLogger());

        $getSupplierFilePathMock->expects($this->once())->method('__invoke')->willReturn('path/to/file.xlsx');
        $downloadStoredProductFileMock->method('__invoke')->with('path/to/file.xlsx')->willThrowException(new \RuntimeException());

        $this->expectException(SupplierFileDownloadError::class);
        ($sut)(new DownloadProductFile('file-identifier'));

        $this->assertEmpty($eventDispatcher->getDispatchedEvents());
    }
}
