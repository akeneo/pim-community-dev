<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierExport;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierExportIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoSupplierToExport(): void
    {
        static::assertCount(0, $this->get(GetSupplierExport::class)());
    }

    /** @test */
    public function itGetsSupplierExport(): void
    {
        $this->createSupplier();
        $this->createContributor('foo1@foo.bar');
        $this->createContributor('foo2@foo.bar');

        $suppliers = $this->get(GetSupplierExport::class)();

        static::assertCount(1, $suppliers);
        static::assertSame('supplier_code', $suppliers[0]->code);
        static::assertSame('Supplier code', $suppliers[0]->label);
        static::assertSame(['foo1@foo.bar', 'foo2@foo.bar'], $suppliers[0]->contributors);
    }

    private function createSupplier(): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_onboarder_serenity_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => '44ce8069-8da1-4986-872f-311737f46f02',
                'code' => 'supplier_code',
                'label' => 'Supplier code',
            ],
        );
    }

    private function createContributor(string $email): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_onboarder_serenity_supplier_contributor` (email, supplier_identifier)
            VALUES (:email, :supplierIdentifier)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'email' => $email,
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f02',
            ],
        );
    }
}
