<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithHasUnreadComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use PHPUnit\Framework\TestCase;

final class ProductFileWithHasUnreadCommentsTest extends TestCase
{
    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new ProductFileWithHasUnreadComments(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'supplier-file.xlsx',
            'path/to/supplier-file.xlsx',
            'contributor@example.com',
            '44ce8069-8da1-4986-872f-311737f46f02',
            '2022-07-12 14:55:46',
            false,
            null,
        );

        static::assertSame(
            [
                'identifier' => 'b8b13d0b-496b-4a7c-a574-0d522ba90752',
                'originalFilename' => 'supplier-file.xlsx',
                'path' => 'path/to/supplier-file.xlsx',
                'uploadedByContributor' => 'contributor@example.com',
                'uploadedBySupplier' => '44ce8069-8da1-4986-872f-311737f46f02',
                'uploadedAt' => '2022-07-12 14:55:46',
                'hasUnreadComments' => false,
                'importStatus' => ProductFileImportStatus::TO_IMPORT->value,
            ],
            $sut->toArray(),
        );
    }

    /** @test */
    public function itContainsHasUnreadCommentsProperty(): void
    {
        $productFileWithHasUnreadComments = new \ReflectionClass(
            ProductFileWithHasUnreadComments::class,
        );
        $properties = $productFileWithHasUnreadComments->getProperties();

        static::assertCount(8, $properties);
        static::assertSame('hasUnreadComments', $properties[6]->getName());
    }
}
