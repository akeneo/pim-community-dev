<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Supplier\Identifier;
use Akeneo\OnboarderSerenity\Test\Common\Fake\InMemorySupplierRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CreateSupplierHandlerTest extends KernelTestCase
{
    /** @test */
    public function itCreatesANewSupplier(): void
    {
        $supplierRepository = new InMemorySupplierRepository();

        $sut = new CreateSupplierHandler($supplierRepository);
        ($sut)(new CreateSupplier(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
            'supplier_code',
            'Supplier label'
        ));

        static::assertNotNull(
            $supplierRepository->find(Identifier::fromString('01319d4c-81c4-4f60-a992-41ea3546824c'))
        );
    }
}
