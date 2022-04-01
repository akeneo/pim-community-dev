<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfThereIsNoSupplier(): void
    {
        static::assertNull(($this->get(GetSupplier::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        ));
    }

    /** @test */
    public function itGetsASupplierWithContributors(): void
    {
        $this->createSupplier();
        $this->createContributor('contributor1@akeneo.com');
        $this->createContributor('contributor2@akeneo.com');

        $supplier = ($this->get(GetSupplier::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier code', $supplier->label);
        static::assertEquals(
            [
                'contributor1@akeneo.com',
                'contributor2@akeneo.com',
            ],
            array_values($supplier->contributors)
        );
    }

    /** @test */
    public function itGetsASupplierWithNoContributors(): void
    {
        $this->createSupplier();

        $supplier = ($this->get(GetSupplier::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier code', $supplier->label);
        static::assertSame([], $supplier->contributors);
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
                'label' => 'Supplier code'
            ]
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
            ]
        );
    }
}
