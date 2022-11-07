<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileImport\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImportConfiguration;
use PHPUnit\Framework\TestCase;

final class ProductFileImportTest extends TestCase
{
    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new ProductFileImportConfiguration(
            'product-file-import',
            'Product File Import 1',
        );

        static::assertSame(
            [
                'code' => 'product-file-import',
                'label' => 'Product File Import 1',
            ],
            $sut->toArray(),
        );
    }
}
