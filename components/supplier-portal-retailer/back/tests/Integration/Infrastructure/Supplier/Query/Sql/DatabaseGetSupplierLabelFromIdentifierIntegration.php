<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierLabelFromIdentifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetSupplierLabelFromIdentifierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfThereIsNoSupplierForTheGivenIdentifier(): void
    {
        static::assertNull(($this->get(GetSupplierLabelFromIdentifier::class))('2f1eeedd-f26f-41e8-be39-72cf53e08a2f'));
    }

    /** @test */
    public function itGetsTheSupplierLabelFromItsIdentifier(): void
    {
        $supplierRepository = $this->get(Repository::class);

        $supplierRepository->save(Supplier::create(
            'c3186dd4-fece-4935-8aad-2b1ccc62ee0d',
            'supplier_code',
            'Supplier name',
            [],
        ));

        static::assertSame(
            'Supplier name',
            $this->get(GetSupplierLabelFromIdentifier::class)('c3186dd4-fece-4935-8aad-2b1ccc62ee0d'),
        );
    }
}
