<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileForSupplier\DownloadProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\DownloadProductFileForSupplier\DownloadProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Exception\SupplierDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\DownloadProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\DownloadProductFileQuery;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\Exception\ProductFileNotFound;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\Exception\UnableToReadProductFile as UnableToReadProductFileServiceAPI;
use PHPUnit\Framework\TestCase;

final class DownloadProductFileTest extends TestCase
{
    /** @test */
    public function itDownloadsAProductFile(): void
    {
        $queryHandler = $this->createMock(DownloadProductFileHandlerForSupplier::class);
        $sut = new DownloadProductFile($queryHandler);

        $queryHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new DownloadProductFileForSupplier(
                'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                'jimmy@supplier.com',
            ))
            ->willReturn(new ProductFileNameAndResourceFile('product_file.xlsx', new \stdClass()))
        ;

        ($sut)(new DownloadProductFileQuery(
            'e77c4413-a6d5-49e6-a102-8042cf5bd439',
            'jimmy@supplier.com',
        ));
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainProductFileDoesNotExistExceptionOccurred(): void
    {
        $queryHandler = $this->createMock(DownloadProductFileHandlerForSupplier::class);
        $sut = new DownloadProductFile($queryHandler);

        $queryHandler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new ProductFileDoesNotExist());

        try {
            ($sut)(new DownloadProductFileQuery(
                'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                'jimmy@supplier.com',
            ));
        } catch (\Exception $e) {
            self::assertSame(ProductFileNotFound::class, \get_class($e));

            return;
        }

        self::fail(sprintf('Expected a "%s" exception.', ProductFileNotFound::class));
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainSupplierDoesNotExistExceptionOccurred(): void
    {
        $queryHandler = $this->createMock(DownloadProductFileHandlerForSupplier::class);
        $sut = new DownloadProductFile($queryHandler);

        $queryHandler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new SupplierDoesNotExist());

        try {
            ($sut)(new DownloadProductFileQuery(
                'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                'jimmy@supplier.com',
            ));
        } catch (\Exception $e) {
            self::assertSame(ProductFileNotFound::class, \get_class($e));

            return;
        }

        self::fail(sprintf('Expected a "%s" exception.', ProductFileNotFound::class));
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfAnUnableToReadProductFileExceptionOccurred(): void
    {
        $queryHandler = $this->createMock(DownloadProductFileHandlerForSupplier::class);
        $sut = new DownloadProductFile($queryHandler);

        $queryHandler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new UnableToReadProductFile());

        try {
            ($sut)(new DownloadProductFileQuery(
                'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                'jimmy@supplier.com',
            ));
        } catch (\Exception $e) {
            self::assertSame(UnableToReadProductFileServiceAPI::class, \get_class($e));

            return;
        }

        self::fail(sprintf('Expected a "%s" exception.', UnableToReadProductFileServiceAPI::class));
    }
}
