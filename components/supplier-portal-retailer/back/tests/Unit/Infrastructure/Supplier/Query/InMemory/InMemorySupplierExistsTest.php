<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemorySupplierExists;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use PHPUnit\Framework\TestCase;

final class InMemorySupplierExistsTest extends TestCase
{
    /** @test */
    public function itTellsIfASupplierExistsGivenItsCode(): void
    {
        $supplierRepository = new InMemoryRepository();
        $sut = new InMemorySupplierExists($supplierRepository);

        $supplierRepository->save((new SupplierBuilder())->build());

        static::assertTrue(($sut)->fromCode('supplier_code'));
        static::assertFalse(($sut)->fromCode('unknown_supplier_code'));
    }
}
