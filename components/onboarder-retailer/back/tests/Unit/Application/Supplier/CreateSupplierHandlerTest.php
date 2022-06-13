<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Retailer\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Retailer\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Query\InMemory\InMemorySupplierExists;
use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
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

        $identifier = Identifier::fromString('01319d4c-81c4-4f60-a992-41ea3546824c');

        $eventDispatcherSpy
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new ContributorAdded($identifier, 'contributor1@example.com')],
                [new ContributorAdded($identifier, 'contributor2@example.com')],
            );

        $sut = new CreateSupplierHandler($supplierRepository, $supplierExists, $eventDispatcherSpy, new NullLogger());
        ($sut)(new CreateSupplier(
            (string) $identifier,
            'supplier_code',
            'Supplier label',
            ['contributor1@example.com', 'contributor2@example.com'],
        ));

        $supplier = $supplierRepository->find(
            Identifier::fromString(
                '01319d4c-81c4-4f60-a992-41ea3546824c',
            ),
        );

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
            '01319d4c-81c4-4f60-a992-41ea3546824c',
            'code',
            'label',
            [],
        ));
    }
}
