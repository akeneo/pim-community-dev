<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierList;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model\SupplierListItem;
use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetSupplierListIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoSupplier(): void
    {
        static::assertCount(0, ($this->get(GetSupplierList::class))());
    }

    /** @test */
    public function itGetsNoMoreThanFiftySuppliersAtATime(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);

        for ($i = 1; $i <= 60; $i++) {
            $supplierRepository->save(Write\Supplier\Model\Supplier::create(
                Uuid::uuid4()->toString(),
                sprintf('supplier_code_%d', $i),
                sprintf('Supplier %d label', $i),
                [],
            ));
        }

        static::assertCount(50, $this->get(GetSupplierList::class)());
    }

    /** @test */
    public function itSearchesOnSupplierLabel(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            Uuid::uuid4()->toString(),
            'walter_white',
            'Walter White',
            [],
        ));

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            Uuid::uuid4()->toString(),
            'jessie_pinkman',
            'Jessie Pinkman',
            [],
        ));

        static::assertSame($this->get(GetSupplierList::class)(1, 'Pin')[0]->code, 'jessie_pinkman');
    }

    /** @test */
    public function itPaginatesTheSupplierList(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);

        for ($i = 1; $i <= 110; $i++) {
            $supplierRepository->save(Write\Supplier\Model\Supplier::create(
                Uuid::uuid4()->toString(),
                sprintf('supplier_code_%d', $i),
                sprintf('Supplier %d label', $i),
                [],
            ));
        }

        $suppliers = $this->get(GetSupplierList::class)(3);

        static::assertCount(10, $suppliers);
    }

    /** @test */
    public function itSortsTheSupplierListInAnAscendingDirection(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            Uuid::uuid4()->toString(),
            'supplier_code_b',
            'Supplier B label',
            [],
        ));

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            Uuid::uuid4()->toString(),
            'supplier_code_a',
            'Supplier A label',
            [],
        ));

        $suppliers = $this->get(GetSupplierList::class)();

        static::assertSame('supplier_code_a', $suppliers[0]->code);
        static::assertSame('supplier_code_b', $suppliers[1]->code);
    }

    /** @test */
    public function itReturnsAContributorCountBySupplier(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f02', 'supplier_2', 'Supplier 2');
        $this->createContributor('contributor1@example.com');
        $this->createContributor('contributor2@example.com');

        $suppliers = $this->get(GetSupplierList::class)();

        static::assertEquals(
            new SupplierListItem(
                '44ce8069-8da1-4986-872f-311737f46f00',
                'supplier_1',
                'Supplier 1',
                0,
            ),
            $suppliers[0],
        );
        static::assertEquals(
            new SupplierListItem(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'supplier_2',
                'Supplier 2',
                2,
            ),
            $suppliers[1],
        );
    }

    private function createSupplier(string $identifier, string $code, string $label): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_onboarder_serenity_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $identifier,
                'code' => $code,
                'label' => $label,
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
        ;
    }
}
