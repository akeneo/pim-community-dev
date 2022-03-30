<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Contributor\Repository\Sql;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itCreatesAndFindsAContributor(): void
    {
        $contributorRepository = $this->get(Contributor\Repository::class);
        $supplierRepository = $this->get(Supplier\Repository::class);

        $supplierRepository->save(Supplier\Model\Supplier::create(
            '44ce8069-8da1-4986-872f-311737f46f01',
            'supplier_code',
            'Supplier code'
        ));

        $contributorRepository->save(Contributor\Model\Contributor::create(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'foo@foo.bar',
            '44ce8069-8da1-4986-872f-311737f46f01'
        ));

        $contributorRepository->save(Contributor\Model\Contributor::create(
            '44ce8069-8da1-4986-872f-311737f46f03',
            'foo@foo.baz',
            '44ce8069-8da1-4986-872f-311737f46f01'
        ));

        $contributor = $this->findContributor('44ce8069-8da1-4986-872f-311737f46f02');

        static::assertInstanceOf(Contributor\Model\Contributor::class, $contributor);
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $contributor->identifier());
        static::assertSame('foo@foo.bar', $contributor->email());
        static::assertSame('44ce8069-8da1-4986-872f-311737f46f01', $contributor->supplierIdentifier());
    }

    /** @test */
    public function itReturnsNullWhenAContributorCannotBeFound(): void
    {
        static::assertNull($this->findContributor('44ce8069-8da1-4986-872f-311737f46f02'));
    }

    private function findContributor(string $identifier): ?Contributor\Model\Contributor
    {
        $sql = <<<SQL
            SELECT identifier, email, supplier_identifier
            FROM `akeneo_onboarder_serenity_supplier_contributor`
            WHERE identifier = :identifier
        SQL;

        $contributor = $this->get(Connection::class)
            ->executeQuery($sql, ['identifier' => $identifier])
            ->fetchAssociative()
        ;

        return false !== $contributor ? Contributor\Model\Contributor::create(
            $contributor['identifier'],
            $contributor['email'],
            $contributor['supplier_identifier'],
        ): null;
    }
}
