<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\Supplier\Write;

use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\CreateSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\CreateSupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemorySupplierExists;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CreateSupplierHandlerTest extends TestCase
{
    private ?UuidFactoryInterface $factory = null;

    protected function tearDown(): void
    {
        if (null !== $this->factory) {
            Uuid::setFactory($this->factory);
        }
    }

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

        $supplier = $supplierRepository->findByCode('supplier_code');

        static::assertSame('supplier_code', $supplier->code());
        static::assertSame('Supplier label', $supplier->label());
    }

    /** @test */
    public function itLogsTheCreationOfANewSupplier(): void
    {
        $supplierRepository = new InMemoryRepository();
        $supplierExists = new InMemorySupplierExists($supplierRepository);
        $eventDispatcherSpy = $this->createMock(EventDispatcher::class);
        $factory = $this->createMock(UuidFactoryInterface::class);
        $uuidInterface = $this->createMock(UuidInterface::class);
        $uuidInterface->method('toString')->willReturn('e36f227c-2946-11e8-b467-0ed5f89f718b');
        $factory
            ->method('uuid4')
            ->willReturn($uuidInterface)
        ;

        $this->factory = Uuid::getFactory();
        Uuid::setFactory($factory);
        $logger = new TestLogger();

        $sut = new CreateSupplierHandler($supplierRepository, $supplierExists, $eventDispatcherSpy, $logger);
        ($sut)(new CreateSupplier(
            'supplier_code',
            'Supplier label',
            ['contributor1@example.com', 'contributor2@example.com'],
        ));

        static::assertTrue($logger->hasInfo([
            'message' => 'Supplier "supplier_code" created.',
            'context' => [
                'data' => [
                    'identifier' => 'e36f227c-2946-11e8-b467-0ed5f89f718b',
                    'supplier_code' => 'supplier_code',
                    'contributor_emails' => ['contributor1@example.com', 'contributor2@example.com'],
                    'metric_key' => 'supplier_created',
                ],
            ],
        ]));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheSupplierAlreadyExists(): void
    {
        $identifier = '01319d4c-81c4-4f60-a992-41ea3546824c';

        $repository = new InMemoryRepository();
        $supplierExists = new InMemorySupplierExists($repository);
        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $repository->save(
            (new SupplierBuilder())
                ->withIdentifier($identifier)
                ->withCode('code')
                ->withLabel('label')
                ->build(),
        );

        $this->expectExceptionObject(new SupplierAlreadyExistsException('code'));

        $sut = new CreateSupplierHandler($repository, $supplierExists, $eventDispatcher, new NullLogger());
        ($sut)(new CreateSupplier(
            'code',
            'label',
            [],
        ));
    }
}
