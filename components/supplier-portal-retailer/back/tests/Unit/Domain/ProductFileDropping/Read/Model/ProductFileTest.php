<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use PHPUnit\Framework\TestCase;

final class ProductFileTest extends TestCase
{
    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new ProductFile(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'supplier-file.xlsx',
            'path/to/supplier-file.xlsx',
            'contributor@example.com',
            '44ce8069-8da1-4986-872f-311737f46f02',
            '2022-07-12 14:55:46',
        );

        static::assertSame(
            [
                'identifier' => 'b8b13d0b-496b-4a7c-a574-0d522ba90752',
                'originalFilename' => 'supplier-file.xlsx',
                'path' => 'path/to/supplier-file.xlsx',
                'uploadedByContributor' => 'contributor@example.com',
                'uploadedBySupplier' => '44ce8069-8da1-4986-872f-311737f46f02',
                'uploadedAt' => '2022-07-12 14:55:46',
                'retailerComments' => [],
                'supplierComments' => [],
                'retailerLastReadAt' => null,
                'supplierLastReadAt' => null,
            ],
            $sut->toArray(),
        );
    }

    /** @test */
    public function itCanContainComments(): void
    {
        $sut = new ProductFile(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'supplier-file.xlsx',
            'path/to/supplier-file.xlsx',
            'contributor@example.com',
            '44ce8069-8da1-4986-872f-311737f46f02',
            '2022-07-12 14:55:46',
            [
                [
                    'author_email' => 'julia@roberts.com',
                    'content' => 'Your product file is awesome!',
                    'created_at' => '2022-09-07 07:59:38',
                ],
            ],
            [
                [
                    'author_email' => 'jimmy@punchline.com',
                    'content' => 'Here are the products I\'ve got for you.',
                    'created_at' => '2022-09-07 08:59:38',
                ],
            ],
            '2022-09-08 08:59:38',
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
                'retailerComments' => [
                    [
                        'author_email' => 'julia@roberts.com',
                        'content' => 'Your product file is awesome!',
                        'created_at' => '2022-09-07 07:59:38',
                    ],
                ],
                'supplierComments' => [
                    [
                        'author_email' => 'jimmy@punchline.com',
                        'content' => 'Here are the products I\'ve got for you.',
                        'created_at' => '2022-09-07 08:59:38',
                    ],
                ],
                'retailerLastReadAt' => '2022-09-08 08:59:38',
                'supplierLastReadAt' => null,
            ],
            $sut->toArray(),
        );
    }
}
