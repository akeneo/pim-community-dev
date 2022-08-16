<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileIsNotDownloadable;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierCodeFromSupplierFileIdentifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class DownloadProductFileHandlerTest extends TestCase
{
    /** @test */
    public function itDownloadsAProductFile(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileName::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $getSupplierCodeFromSupplierFileIdentifier = $this->createMock(
            GetSupplierCodeFromSupplierFileIdentifier::class,
        );
        $eventDispatcher = new StubEventDispatcher();
        $sut = new DownloadProductFileHandler(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
            $eventDispatcher,
            $getSupplierCodeFromSupplierFileIdentifier,
            new NullLogger(),
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
        $getSupplierCodeFromSupplierFileIdentifier
            ->expects($this->once())
            ->method('__invoke')
            ->with('1ed45c7b-6c61-4862-a11c-00c9580a8710')
            ->willReturn('supplier_code')
        ;

        $productFileNameAndResourceFile = ($sut)(
            new DownloadProductFile('1ed45c7b-6c61-4862-a11c-00c9580a8710', 1)
        );
        $this->assertSame(
            'file.xlsx',
            $productFileNameAndResourceFile->originalFilename,
        );
        $this->assertSame(
            $fakeResource,
            $productFileNameAndResourceFile->file,
        );
        $this->assertEquals(
            [
                new ProductFileDownloaded(
                    '1ed45c7b-6c61-4862-a11c-00c9580a8710',
                    'supplier_code',
                    1,
                ),
            ],
            $eventDispatcher->getDispatchedEvents(),
        );
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileDoesNotExistInTheDatabase(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileName::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $getSupplierCodeFromSupplierFileIdentifier = $this->createMock(
            GetSupplierCodeFromSupplierFileIdentifier::class,
        );
        $sut = new DownloadProductFileHandler(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
            new StubEventDispatcher(),
            $getSupplierCodeFromSupplierFileIdentifier,
            new NullLogger(),
        );

        $getProductFilePathAndFileNameMock->expects($this->once())->method('__invoke')->willReturn(null);

        $this->expectException(SupplierFileDoesNotExist::class);
        ($sut)(new DownloadProductFile('file-identifier', 1));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileCouldNotBeRetrieved(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileName::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $getSupplierCodeFromSupplierFileIdentifier = $this->createMock(
            GetSupplierCodeFromSupplierFileIdentifier::class,
        );
        $eventDispatcher = new StubEventDispatcher();
        $sut = new DownloadProductFileHandler(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
            $eventDispatcher,
            $getSupplierCodeFromSupplierFileIdentifier,
            new NullLogger(),
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
            ->willThrowException(new \RuntimeException())
        ;

        $this->expectException(SupplierFileIsNotDownloadable::class);
        ($sut)(new DownloadProductFile('file-identifier', 1));

        $this->assertEmpty($eventDispatcher->getDispatchedEvents());
    }
}
