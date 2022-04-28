<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseSupplierContributorsBelongingToAnotherSupplierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsContributorEmailsThatBelongToAnotherSupplier(): void
    {
        $this->createSupplier();
        $this->createContributor('contributor1@example.com');
        $this->createContributor('contributor2@example.com');

        $this->assertEquals(
            ['contributor1@example.com', 'contributor2@example.com',],
            $this->get(SupplierContributorsBelongingToAnotherSupplier::class)('36fc4dbf-43cb-4246-8966-56ca111d859d', [
                'contributor1@example.com',
                'contributor3@example.com',
                'contributor2@example.com',
            ]),
        );
    }

    /** @test */
    public function itReturnsAnEmptyArrayIfNoExistingContributorAreDetected(): void
    {
        $this->createSupplier();
        $this->createContributor('contributor1@example.com');
        $this->createContributor('contributor2@example.com');

        $this->assertEquals(
            [],
            $this->get(SupplierContributorsBelongingToAnotherSupplier::class)('36fc4dbf-43cb-4246-8966-56ca111d859d', [
                'contributor3@example.com',
                'contributor4@example.com',
            ]),
        );
    }

    /** @test */
    public function itReturnsAnEmptyArrayIfNoContributorEmailsArePassed(): void
    {
        $this->createSupplier();
        $this->createContributor('contributor1@example.com');
        $this->createContributor('contributor2@example.com');

        $this->assertEmpty(
            $this->get(SupplierContributorsBelongingToAnotherSupplier::class)('36fc4dbf-43cb-4246-8966-56ca111d859d', []),
        );
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
