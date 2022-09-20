<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\GetProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\GetProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithCommentsForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use PHPUnit\Framework\TestCase;

final class GetProductFileHandlerForSupplierTest extends TestCase
{
    /** @test */
    public function itGetsAProductFile(): void
    {
        $getProductFileWithComments = $this->createMock(GetProductFileWithCommentsForSupplier::class);

        $sut = new GetProductFileHandlerForSupplier($getProductFileWithComments);

        $getProductFileWithComments
            ->expects(static::once())
            ->method('__invoke')
            ->with('72d6e085-d64f-4e14-8fb7-9095519262fb')
            ->willReturn(
                new ProductFile(
                    '72d6e085-d64f-4e14-8fb7-9095519262fb',
                    'file.xlsx',
                    'path/to/file.xlsx',
                    'jimmy.punchline@los-pollos-hermanos.com',
                    '92c423ea-d5f7-46a8-a623-62de6d89bddf',
                    '2022-09-19 17:14:00',
                    [],
                    [],
                ),
            )
        ;


        ($sut)(new GetProductFileForSupplier('72d6e085-d64f-4e14-8fb7-9095519262fb'));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheProductFileDoesNotExist(): void
    {
        $getProductFileWithComments = $this->createMock(GetProductFileWithCommentsForSupplier::class);

        $sut = new GetProductFileHandlerForSupplier($getProductFileWithComments);

        $getProductFileWithComments
            ->expects($this->once())
            ->method('__invoke')
            ->with('72d6e085-d64f-4e14-8fb7-9095519262fb')
            ->willReturn(null)
        ;

        static::expectException(ProductFileDoesNotExist::class);
        ($sut)(new GetProductFileForSupplier('72d6e085-d64f-4e14-8fb7-9095519262fb'));
    }
}
