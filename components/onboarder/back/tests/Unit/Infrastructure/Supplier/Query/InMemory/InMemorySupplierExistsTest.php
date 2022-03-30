<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Code;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory\InMemorySupplierExists;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemorySupplierExistsTest extends TestCase
{
    /** @test */
    public function itTellsIfASupplierExistsGivenItsCode(): void
    {
        $supplierRepository = new InMemoryRepository();
        $sut = new InMemorySupplierExists($supplierRepository);

        $supplierRepository->save(
            Supplier::create(
                'ca8baefd-0e05-4683-be48-6b9ff87e4cbc',
                'supplier_code',
                'Supplier label'
            )
        );

        static::assertTrue(($sut)->fromCode(Code::fromString('supplier_code')));
        static::assertFalse(($sut)->fromCode(Code::fromString('unknown_supplier_code')));
    }
}
