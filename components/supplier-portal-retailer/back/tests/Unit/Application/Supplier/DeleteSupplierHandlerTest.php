<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\Supplier;

use Akeneo\SupplierPortal\Retailer\Application\Supplier\DeleteSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\DeleteSupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\StubEventDispatcher;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class DeleteSupplierHandlerTest extends TestCase
{
    /** @test */
    public function itDeletesASupplier(): void
    {
        $supplierIdentifier = Identifier::fromString(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
        );

        $supplierRepositorySpy = $this->createMock(Repository::class);
        $supplierRepositorySpy->expects($this->once())->method('delete')->with($supplierIdentifier);

        $supplier = Supplier::create(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
            'mysupplier',
            'My Supplier',
            ['contributor1@example.com', 'contributor2@example.com'],
        );
        $supplierRepository = new InMemoryRepository();
        $supplierRepository->save($supplier);

        $eventDispatcher = new StubEventDispatcher();

        $sut = new DeleteSupplierHandler(
            $supplierRepositorySpy,
            new InMemoryGetSupplierWithContributors($supplierRepository),
            $eventDispatcher,
            new NullLogger(),
        );

        ($sut)(new DeleteSupplier((string) $supplierIdentifier));

        $this->assertEquals(
            [
                new ContributorDeleted($supplierIdentifier, 'contributor1@example.com'),
                new ContributorDeleted($supplierIdentifier, 'contributor2@example.com'),
            ],
            $eventDispatcher->getDispatchedEvents(),
        );
    }
}
