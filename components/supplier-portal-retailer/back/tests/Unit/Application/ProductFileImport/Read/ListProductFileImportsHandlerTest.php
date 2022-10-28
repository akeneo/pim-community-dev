<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileImport\Read;


use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Read\ListProductFileImports\ListProductFileImports;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Read\ListProductFileImports\ListProductFileImportsHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\FindAllProductFileImports;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImport;
use PHPUnit\Framework\TestCase;

class ListProductFileImportsHandlerTest extends TestCase
{
    /** @test */
    public function itGetProductFileImport(): void
    {
        $findAllProductFileImports = $this->createMock(FindAllProductFileImports::class);
        $findAllProductFileImports
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn([
                new ProductFileImport('import1', 'Import 1'),
                new ProductFileImport('import2', 'Import 2'),
            ]);

        $sut = new ListProductFileImportsHandler($findAllProductFileImports);

        $this->assertEquals([
            new ProductFileImport('import1', 'Import 1'),
            new ProductFileImport('import2', 'Import 2'),
        ], ($sut)(new ListProductFileImports()));
    }

}
