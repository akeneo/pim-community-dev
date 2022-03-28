<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\SupplierExists;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;

final class DatabaseSupplierExistsIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itTellsIfASupplierExistsGivenItsCode(): void
    {
        $supplierRepository = $this->get(Supplier\Repository::class);

        $supplierRepository->save(Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code',
            [],
        ));

        static::assertTrue(
            $this->get(SupplierExists::class)
                ->fromCode(Supplier\ValueObject\Code::fromString('supplier_code'))
        );
        static::assertFalse(
            $this->get(SupplierExists::class)
                ->fromCode(Supplier\ValueObject\Code::fromString('unknown_supplier_code'))
        );
    }
}
