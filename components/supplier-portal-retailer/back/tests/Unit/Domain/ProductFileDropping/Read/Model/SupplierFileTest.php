<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\SupplierFile;
use PHPUnit\Framework\TestCase;

final class SupplierFileTest extends TestCase
{
    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new SupplierFile(
            'supplier-file.xlsx',
            '44ce8069-8da1-4986-872f-311737f46f01',
            '44ce8069-8da1-4986-872f-311737f46f02',
            '2022-07-12 14:55:46',
        );

        static::assertSame(
            [
                'filename' => 'supplier-file.xlsx',
                'uploadedByContributor' => '44ce8069-8da1-4986-872f-311737f46f01',
                'uploadedBySupplier' => '44ce8069-8da1-4986-872f-311737f46f02',
                'uploadedAt' => '2022-07-12 14:55:46',
            ],
            $sut->toArray(),
        );
    }
}
