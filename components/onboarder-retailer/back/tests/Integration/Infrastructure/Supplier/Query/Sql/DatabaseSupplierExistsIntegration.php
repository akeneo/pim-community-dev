<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\SupplierExists;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\OnboarderSerenity\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseSupplierExistsIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itTellsIfASupplierExistsGivenItsCode(): void
    {
        $supplierRepository = $this->get(Repository::class);

        $supplierRepository->save(Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code',
            [],
        ));

        static::assertTrue(
            $this->get(SupplierExists::class)
                ->fromCode(Code::fromString('supplier_code')),
        );
        static::assertFalse(
            $this->get(SupplierExists::class)
                ->fromCode(Code::fromString('unknown_supplier_code')),
        );
    }
}
