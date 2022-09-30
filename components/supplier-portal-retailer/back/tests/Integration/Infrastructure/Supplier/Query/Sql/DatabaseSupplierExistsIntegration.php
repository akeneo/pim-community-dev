<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\SupplierExists;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseSupplierExistsIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itTellsIfASupplierExistsGivenItsCode(): void
    {
        ($this->get(Repository::class))->save((new SupplierBuilder())->build());

        static::assertTrue($this->get(SupplierExists::class)->fromCode('supplier_code'));
        static::assertFalse($this->get(SupplierExists::class)->fromCode('unknown_supplier_code'));
    }
}
