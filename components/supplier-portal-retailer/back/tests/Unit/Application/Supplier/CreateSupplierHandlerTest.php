<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\Supplier;

use Akeneo\SupplierPortal\Retailer\Application\Supplier\CreateSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\CreateSupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemorySupplierExists;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CreateSupplierHandlerTest extends TestCase
{
    /** @test */
    public function itCreatesANewSupplier(): void
    {
        $supplierRepository = new InMemoryRepository();
        $supplierExists = new InMemorySupplierExists($supplierRepository);
        $eventDispatcherSpy = $this->createMock(EventDispatcher::class);

        $eventDispatcherSpy
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(ContributorAdded::class)],
                [$this->isInstanceOf(ContributorAdded::class)],
            );

        $sut = new CreateSupplierHandler($supplierRepository, $supplierExists, $eventDispatcherSpy, new NullLogger());
        ($sut)(new CreateSupplier(
            'supplier_code',
            'Supplier label',
            ['contributor1@example.com', 'contributor2@example.com'],
        ));

        $supplier = $supplierRepository->findByCode(Code::fromString('supplier_code'));

        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier label', $supplier->label());
    }

    /** @test */
    public function itThrowsAnExceptionIfTheSupplierAlreadyExists(): void
    {
        $identifier = '01319d4c-81c4-4f60-a992-41ea3546824c';

        $repository = new InMemoryRepository();
        $supplierExists = new InMemorySupplierExists($repository);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $repository->save(Supplier::create($identifier, 'code', 'label', []));

        $this->expectExceptionObject(new SupplierAlreadyExistsException('code'));

        $sut = new CreateSupplierHandler($repository, $supplierExists, $eventDispatcher, new NullLogger());
        ($sut)(new CreateSupplier(
            'code',
            'label',
            [],
        ));
    }
}
