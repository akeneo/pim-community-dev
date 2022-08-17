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
        $identifier = Identifier::fromString(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
        );

        $spy = $this->createMock(Repository::class);
        $spy->expects($this->once())->method('delete')->with($identifier);

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
            $spy,
            new InMemoryGetSupplierWithContributors($supplierRepository),
            $eventDispatcher,
            new NullLogger(),
        );

        ($sut)(new DeleteSupplier((string) $identifier));

        $this->assertEquals(
            [
                new ContributorDeleted($identifier, 'contributor1@example.com'),
                new ContributorDeleted($identifier, 'contributor2@example.com'),
            ],
            $eventDispatcher->getDispatchedEvents(),
        );
    }
}
