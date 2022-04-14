<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Exception\SupplierAlreadyExistsException;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory\InMemorySupplierExists;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class CreateSupplierHandlerTest extends TestCase
{
    /** @test */
    public function itCreatesANewSupplier(): void
    {
        $supplierRepository = new InMemoryRepository();
        $supplierExists = new InMemorySupplierExists($supplierRepository);

        $sut = new CreateSupplierHandler($supplierRepository, $supplierExists);
        ($sut)(new CreateSupplier(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
            'supplier_code',
            'Supplier label',
            [],
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

        $repository->save(Supplier::create($identifier, 'code', 'label', []));

        $this->expectExceptionObject(new SupplierAlreadyExistsException('code'));

        $sut = new CreateSupplierHandler($repository, $supplierExists);
        ($sut)(new CreateSupplier(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
            'code',
            'label',
            [],
        ));
    }
}
