<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFile;
use PHPUnit\Framework\TestCase;

final class SupplierFileTest extends TestCase
{
    /** @test */
    public function itCanBeNormalized(): void
    {
        $sut = new SupplierFile(
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655supplier-file.xlsx',
            '44ce8069-8da1-4986-872f-311737f46f01',
            '2022-07-12 14:55:46',
        );

        static::assertSame(
            [
                'filename' => 'supplier-file.xlsx',
                'path' => '2/f/a/4/2fa4afe5465afe5655supplier-file.xlsx',
                'uploadedByContributor' => '44ce8069-8da1-4986-872f-311737f46f01',
                'uploadedAt' => '2022-07-12 14:55:46',
            ],
            $sut->toArray(),
        );
    }
}
