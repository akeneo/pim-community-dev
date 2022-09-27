<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itCreatesAndFindsASupplier(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f03')
                ->withCode('other_supplier_code')
                ->withLabel('Other supplier label')
                ->build(),
        );

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
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->build(),
        );
        $supplierBeforeUpdate = $this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02');
        $updatedAtBeforeUpdate = $supplierBeforeUpdate['updated_at'];
        sleep(1);

        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->withCode('new_supplier_code')
                ->withLabel('New supplier label')
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );

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
    public function itFindsASupplier(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f03')
                ->withCode('other_supplier_code')
                ->withLabel('Other supplier label')
                ->build(),
        );

        $supplier = $supplierRepository->find(
            Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02',
            ),
        );

        static::assertInstanceOf(Supplier::class, $supplier);
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier label', $supplier->label());
        static::assertSame([
            ['email' => 'contributor1@example.com'],
            ['email' => 'contributor2@example.com'],
        ], $supplier->contributors());

        $supplier2 = $supplierRepository->find(
            Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f03',
            ),
        );
        static::assertCount(0, $supplier2->contributors());
    }

    /** @test */
    public function itDeletesASupplierAndItsContributors(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f01')
                ->withCode('other_supplier_code')
                ->withLabel('Other supplier label')
                ->build(),
        );

        $this->get(Repository::class)->delete(
            Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02',
            ),
        );
        static::assertNull($this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02'));
        $this->assertSupplierContributorCount('44ce8069-8da1-4986-872f-311737f46f02', 0);

        static::assertIsArray($this->findSupplier('44ce8069-8da1-4986-872f-311737f46f01'));
    }

    private function findSupplier(string $identifier): ?array
    {
        $sql = <<<SQL
            SELECT code, label, updated_at
            FROM `akeneo_supplier_portal_supplier`
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
            FROM akeneo_supplier_portal_supplier_contributor
            WHERE supplier_identifier = :supplierIdentifier
        SQL;

        $contributorCount = $this->get(Connection::class)
            ->executeQuery($sql, ['supplierIdentifier' => $supplierIdentifier])
            ->fetchOne();
        ;
        static::assertSame($expectedCount, (int) $contributorCount);
    }
}
