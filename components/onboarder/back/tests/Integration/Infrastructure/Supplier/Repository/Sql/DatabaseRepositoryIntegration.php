<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Repository\Sql;

use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itCreatesAndFindsASupplier(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier label',
            ['contributor1@akeneo.com', 'contributor2@akeneo.com'],
        ));

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f03',
            'other_supplier_code',
            'Other supplier label',
            [],
        ));

        $supplier = $this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02');
        static::assertSame('supplier_code', $supplier['code']);
        static::assertSame('Supplier label', $supplier['label']);
        $this->assertSupplierContributorCount('44ce8069-8da1-4986-872f-311737f46f02', 2);

        $supplier = $this->findSupplier('44ce8069-8da1-4986-872f-311737f46f03');
        static::assertSame('other_supplier_code', $supplier['code']);
        static::assertSame('Other supplier label', $supplier['label']);
        $this->assertSupplierContributorCount('44ce8069-8da1-4986-872f-311737f46f03', 0);
    }

    /** @test */
    public function itUpdatesAnExistingSupplier(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier label',
            [],
        ));
        $supplierBeforeUpdate = $this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02');
        $updatedAtBeforeUpdate = $supplierBeforeUpdate['updated_at'];
        sleep(1);

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'new_supplier_code',
            'New supplier label',
            ['contributor1@akeneo.com', 'contributor2@akeneo.com'],
        ));

        $supplier = $this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02');
        $updatedAtAfterUpdate = $supplier['updated_at'];

        static::assertSame('new_supplier_code', $supplier['code']);
        static::assertSame('New supplier label', $supplier['label']);
        $this->assertSupplierContributorCount('44ce8069-8da1-4986-872f-311737f46f02', 2);
        static::assertGreaterThan($updatedAtBeforeUpdate, $updatedAtAfterUpdate);
    }

    /** @test */
    public function itReturnsNullWhenASupplierCannotBeFound(): void
    {
        static::assertNull($this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02'));
    }

    /** @test */
    public function itGetsASUpplierByItsIdentifier(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier label',
            ['contributor1@akeneo.com', 'contributor2@akeneo.com'],
        ));
        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f03',
            'other_supplier_code',
            'Other supplier label',
            [],
        ));

        $supplier = $supplierRepository->find(
            Write\Supplier\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02'
            )
        );

        static::assertInstanceOf(Write\Supplier\Model\Supplier::class, $supplier);
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier label', $supplier->label());
        static::assertSame([
            ['email' => 'contributor1@akeneo.com'],
            ['email' => 'contributor2@akeneo.com'],
        ], $supplier->contributors());

        $supplier2 = $supplierRepository->find(
            Write\Supplier\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f03'
            )
        );
        static::assertCount(0, $supplier2->contributors());
    }

    /** @test */
    public function itDeletesASupplierAndItsContributors(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);
        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier label',
            ['contributor1@akeneo.com', 'contributor2@akeneo.com'],
        ));
        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f01',
            'other_supplier_code',
            'Other supplier label',
            [],
        ));

        $this->get(Write\Supplier\Repository::class)->delete(
            Write\Supplier\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02'
            )
        );
        static::assertNull($this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02'));
        $this->assertSupplierContributorCount('44ce8069-8da1-4986-872f-311737f46f02', 0);

        static::assertIsArray($this->findSupplier('44ce8069-8da1-4986-872f-311737f46f01'));
    }

    private function findSupplier(string $identifier): ?array
    {
        $sql = <<<SQL
            SELECT code, label, updated_at
            FROM `akeneo_onboarder_serenity_supplier`
            WHERE identifier = :identifier
        SQL;

        $supplier = $this->get(Connection::class)
            ->executeQuery($sql, ['identifier' => $identifier])
            ->fetchAssociative()
        ;

        return $supplier ?: null;
    }

    private function assertSupplierContributorCount(string $supplierIdentifier, int $expectedCount): void
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM akeneo_onboarder_serenity_supplier_contributor
            WHERE supplier_identifier = :supplierIdentifier
        SQL;

        $contributorNumber = $this->get(Connection::class)
            ->executeQuery($sql, ['supplierIdentifier' => $supplierIdentifier])
            ->fetchOne();
        ;
        static::assertSame($expectedCount, (int) $contributorNumber);
    }
}
