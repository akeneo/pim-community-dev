<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierExists;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;

final class DatabaseSupplierExistsIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itTellsIfASupplierExistsGivenItsCode(): void
    {
        $supplierRepository = $this->get(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\Repository::class);

        $supplierRepository->save(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code',
            [],
        ));

        static::assertTrue(
            $this->get(SupplierExists::class)
                ->fromCode(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString('supplier_code')),
        );
        static::assertFalse(
            $this->get(SupplierExists::class)
                ->fromCode(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString('unknown_supplier_code')),
        );
    }
}
