<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseSupplierContributorsBelongingToAnotherSupplierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsContributorEmailsThatBelongToAnotherSupplier(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );

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
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );

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
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );

        $this->assertEmpty(
            $this->get(SupplierContributorsBelongingToAnotherSupplier::class)('36fc4dbf-43cb-4246-8966-56ca111d859d', []),
        );
    }
}
