<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Read;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DownloadStoredProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileNameForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Exception\SupplierDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use PHPUnit\Framework\TestCase;

final class DownloadProductFileHandlerForSupplierTest extends TestCase
{
    /** @test */
    public function itDownloadsAProductFile(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileNameForSupplier::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $getSupplierFromContributorEmail = $this->createMock(GetSupplierFromContributorEmail::class);

        $sut = new DownloadProductFileHandlerForSupplier(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
            $getSupplierFromContributorEmail,
        );

        $getProductFilePathAndFileNameMock
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(
                new ProductFilePathAndFileName('file.xlsx', 'path/to/file.xlsx'),
            )
        ;

        $getSupplierFromContributorEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(
                new Supplier('e36f227c-2946-11e8-b467-0ed5f89f718b', 'a-supplier', 'A supplier'),
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
            new DownloadProductFileForSupplier(
                '1ed45c7b-6c61-4862-a11c-00c9580a8710',
                'contributor@example.com',
            )
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
    public function itThrowsAnExceptionIfTheSupplierDoesNotExist(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileNameForSupplier::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $getSupplierFromContributorEmail = $this->createMock(GetSupplierFromContributorEmail::class);

        $sut = new DownloadProductFileHandlerForSupplier(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
            $getSupplierFromContributorEmail,
        );

        $getSupplierFromContributorEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(null)
        ;

        $this->expectException(SupplierDoesNotExist::class);
        ($sut)(new DownloadProductFileForSupplier(
            '1ed45c7b-6c61-4862-a11c-00c9580a8710',
            'contributor@example.com',
        )
        );
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileDoesNotExistInTheDatabase(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileNameForSupplier::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $getSupplierFromContributorEmail = $this->createMock(GetSupplierFromContributorEmail::class);

        $sut = new DownloadProductFileHandlerForSupplier(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
            $getSupplierFromContributorEmail,
        );

        $getSupplierFromContributorEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(
                new Supplier('e36f227c-2946-11e8-b467-0ed5f89f718b', 'a-supplier', 'A supplier'),
            )
        ;

        $getProductFilePathAndFileNameMock->expects($this->once())->method('__invoke')->willReturn(null);

        $this->expectException(ProductFileDoesNotExist::class);
        ($sut)(new DownloadProductFileForSupplier(
            '1ed45c7b-6c61-4862-a11c-00c9580a8710',
            'contributor@example.com',
        ));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheFileCouldNotBeRetrieved(): void
    {
        $getProductFilePathAndFileNameMock = $this->createMock(GetProductFilePathAndFileNameForSupplier::class);
        $downloadStoredProductFileMock = $this->createMock(DownloadStoredProductFile::class);
        $getSupplierFromContributorEmail = $this->createMock(GetSupplierFromContributorEmail::class);

        $sut = new DownloadProductFileHandlerForSupplier(
            $getProductFilePathAndFileNameMock,
            $downloadStoredProductFileMock,
            $getSupplierFromContributorEmail,
        );

        $getSupplierFromContributorEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(
                new Supplier('e36f227c-2946-11e8-b467-0ed5f89f718b', 'a-supplier', 'A supplier'),
            )
        ;

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
        ($sut)(new DownloadProductFileForSupplier(
            '1ed45c7b-6c61-4862-a11c-00c9580a8710',
            'contributor@example.com',
        ));
    }
}
