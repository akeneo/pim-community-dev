<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemorySupplierExists;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemorySupplierExistsTest extends TestCase
{
    /** @test */
    public function itTellsIfASupplierExistsGivenItsCode(): void
    {
        $supplierRepository = new InMemoryRepository();
        $sut = new InMemorySupplierExists($supplierRepository);

        $supplierRepository->save(
            Supplier::create(
                'ca8baefd-0e05-4683-be48-6b9ff87e4cbc',
                'supplier_code',
                'Supplier label',
                [],
            ),
        );

        static::assertTrue(($sut)->fromCode('supplier_code'));
        static::assertFalse(($sut)->fromCode('unknown_supplier_code'));
    }
}
