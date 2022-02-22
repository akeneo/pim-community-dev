<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Identifier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Supplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\SupplierRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class InMemorySupplierRepositoryTest extends KernelTestCase
{
    /** @test */
    public function itCreatesASupplierAndReturnsIt(): void
    {
        $supplierRepository = $this->getContainer()->get(SupplierRepository::class);

        $supplierRepository->add(
            Supplier::create(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'supplier_code',
                'Supplier code'
            )
        );

        $supplier = $supplierRepository->find(Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02'));

        static::assertInstanceOf(Supplier::class, $supplier);
    }

    /** @test */
    public function itReturnsNullWhenASupplierCannotBefound(): void
    {
        static::assertNull($this->getContainer()->get(SupplierRepository::class)
            ->find(Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')));

    }
}
