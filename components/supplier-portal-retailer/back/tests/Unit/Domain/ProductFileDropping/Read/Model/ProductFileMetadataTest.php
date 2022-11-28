<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileMetadata;
use PHPUnit\Framework\TestCase;

final class ProductFileMetadataTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheNumberOfRowsAndTheNumberOfColumns(): void
    {
        $productFileMetadataReflectionClass = new \ReflectionClass(ProductFileMetadata::class);
        $properties = $productFileMetadataReflectionClass->getProperties();

        static::assertCount(2, $properties);
        static::assertSame(
            'numberOfRows',
            $properties[0]->getName(),
        );
        static::assertSame(
            'numberOfColumns',
            $properties[1]->getName(),
        );
    }

    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new ProductFileMetadata(10, 12);

        self::assertSame([
            'number_of_rows' => 10,
            'number_of_columns' => 12,
        ], $sut->toArray());
    }
}
