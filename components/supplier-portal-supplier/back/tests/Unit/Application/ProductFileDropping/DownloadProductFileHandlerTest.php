<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\DownloadProductFile;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePath;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class DownloadProductFileHandlerTest extends TestCase
{
    /** @test */
    public function itDownloadsAProductFile(): void
    {
        $getProductFilePathMock = $this->createMock(GetProductFilePath::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $sut = new DownloadProductFileHandler($getProductFilePathMock, $downloadStoredProductFileMock, new NullLogger());

        $getProductFilePathMock
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(Path::fromString('path/to/file.xlsx'))
        ;
        $fakeResource = new \stdClass();
        $downloadStoredProductFileMock
            ->expects($this->once())
            ->method('__invoke')
            ->with('path/to/file.xlsx')
            ->willReturn($fakeResource)
        ;

        $this->assertSame(
            $fakeResource,
            ($sut)(new DownloadProductFile('63c5e1d5-b804-4d24-b0b2-47c4aad3f536')),
        );
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileDoesNotExistInTheDatabase(): void
    {
        $getProductFilePathMock = $this->createMock(GetProductFilePath::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $sut = new DownloadProductFileHandler($getProductFilePathMock, $downloadStoredProductFileMock, new NullLogger());

        $getProductFilePathMock->expects($this->once())->method('__invoke')->willReturn(null);

        $this->expectException(ProductFileDoesNotExist::class);
        ($sut)(new DownloadProductFile('63c5e1d5-b804-4d24-b0b2-47c4aad3f536'));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileCouldNotBeRetrieved(): void
    {
        $getProductFilePathMock = $this->createMock(GetProductFilePath::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $sut = new DownloadProductFileHandler($getProductFilePathMock, $downloadStoredProductFileMock, new NullLogger());

        $getProductFilePathMock
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(Path::fromString('path/to/file.xlsx'))
        ;
        $downloadStoredProductFileMock
            ->method('__invoke')
            ->with(Path::fromString('path/to/file.xlsx'))
            ->willThrowException(new \RuntimeException())
        ;

        $this->expectException(ProductFileIsNotDownloadable::class);
        ($sut)(new DownloadProductFile('63c5e1d5-b804-4d24-b0b2-47c4aad3f536'));
    }
}
