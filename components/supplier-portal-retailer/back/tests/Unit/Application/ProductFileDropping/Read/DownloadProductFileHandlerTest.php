<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Read;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;

final class DownloadProductFileHandlerTest extends TestCase
{
    /** @test */
    public function itDownloadsAProductFile(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileName::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);

        $sut = new DownloadProductFileHandler(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
        );

        $getProductFilePathAndFileNameMock
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(
                new ProductFilePathAndFileName('file.xlsx', 'path/to/file.xlsx'),
            )
        ;
        $fakeResource = new \stdClass();
        $downloadStoredProductFileMock
            ->expects($this->once())
            ->method('__invoke')
            ->with('path/to/file.xlsx')
            ->willReturn($fakeResource)
        ;

        $productFileNameAndResourceFile = ($sut)(
            new DownloadProductFile('1ed45c7b-6c61-4862-a11c-00c9580a8710')
        );
        $this->assertSame(
            'file.xlsx',
            $productFileNameAndResourceFile->originalFilename,
        );
        $this->assertSame(
            $fakeResource,
            $productFileNameAndResourceFile->file,
        );
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileDoesNotExistInTheDatabase(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileName::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);

        $sut = new DownloadProductFileHandler(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
        );

        $getProductFilePathAndFileNameMock->expects($this->once())->method('__invoke')->willReturn(null);

        $this->expectException(ProductFileDoesNotExist::class);
        ($sut)(new DownloadProductFile('file-identifier'));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileCouldNotBeRetrieved(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileName::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);

        $eventDispatcher = new StubEventDispatcher();
        $sut = new DownloadProductFileHandler(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
        );

        $getProductFilePathAndFileNameMock
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(
                new ProductFilePathAndFileName('file.xlsx', 'path/to/file.xlsx'),
            )
        ;
        $downloadStoredProductFileMock
            ->method('__invoke')
            ->with('path/to/file.xlsx')
            ->willThrowException(new UnableToReadProductFile())
        ;

        $this->expectException(UnableToReadProductFile::class);
        ($sut)(new DownloadProductFile('file-identifier'));

        $this->assertEmpty($eventDispatcher->getDispatchedEvents());
    }
}
