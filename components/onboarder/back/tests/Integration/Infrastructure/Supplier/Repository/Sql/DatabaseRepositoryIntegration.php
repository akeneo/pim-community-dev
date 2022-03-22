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
            'Supplier code'
        ));

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f03',
            'other_supplier_code',
            'Other supplier code'
        ));

        $supplier = $this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(Write\Supplier\Model\Supplier::class, $supplier);
        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier code', $supplier->label());
    }

    /** @test */
    public function itUpdatesAnExistingSupplier(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code'
        ));

        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'new_supplier_code',
            'New supplier code'
        ));

        $supplier = $this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertSame('new_supplier_code', $supplier->code());
        static::assertSame('New supplier code', $supplier->label());
    }

    /** @test */
    public function itReturnsNullWhenASupplierCannotBeFound(): void
    {
        static::assertNull($this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02'));
    }

    private function findSupplier(string $identifier): ?Write\Supplier\Model\Supplier
    {
        $sql = <<<SQL
            SELECT identifier, code, label
            FROM `akeneo_onboarder_serenity_supplier`
            WHERE identifier = :identifier
        SQL;

        $supplier = $this->get(Connection::class)
            ->executeQuery($sql, ['identifier' => $identifier])
            ->fetchAssociative()
        ;

        return false !== $supplier ? Write\Supplier\Model\Supplier::create(
            $supplier['identifier'],
            $supplier['code'],
            $supplier['label']
        ): null;
    }

    /** @test */
    public function itDeletesASupplier(): void
    {
        $supplierRepository = $this->get(Write\Supplier\Repository::class);
        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_code',
            'Supplier code'
        ));
        $supplierRepository->save(Write\Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f01',
            'supplier_code2',
            'Supplier code2'
        ));
        $this->get(Write\Supplier\Repository::class)->delete(
            Write\Supplier\ValueObject\Identifier::fromString(
                '44ce8069-8da1-4986-872f-311737f46f02'
            )
        );
        static::assertNull($this->findSupplier('44ce8069-8da1-4986-872f-311737f46f02'));
        static::assertInstanceOf(Write\Supplier\Model\Supplier::class, $this->findSupplier('44ce8069-8da1-4986-872f-311737f46f01'));
    }
}
