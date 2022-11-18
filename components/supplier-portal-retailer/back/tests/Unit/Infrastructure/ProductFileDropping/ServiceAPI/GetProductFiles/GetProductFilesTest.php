<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier\ListProductFilesForSupplierHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier\ProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles\GetProductFiles;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles\GetProductFilesQuery;
use PHPUnit\Framework\TestCase;

final class GetProductFilesTest extends TestCase
{
    /** @test */
    public function itGetsProductFiles(): void
    {
        $listProductFilesQueryHandler = $this->createMock(ListProductFilesForSupplierHandler::class);
        $sut = new GetProductFiles($listProductFilesQueryHandler);
        $productFiles = new ProductFiles([new ProductFile(
            '73db94c5-846e-4b04-8de6-e4dce493c099',
            'product_file.xlsx',
            'path/to/product_file.xlsx',
            'jimmy@supplier.com',
            '8110a4f5-9d1e-488e-a878-1a274423cfa4',
            '',
            null,
        )], 1);

        $listProductFilesQueryHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new ListProductFilesForSupplier('jimmy@supplier.com', 1))
            ->willReturn($productFiles)
        ;

        ($sut)(new GetProductFilesQuery('jimmy@supplier.com', 1));
    }
}
