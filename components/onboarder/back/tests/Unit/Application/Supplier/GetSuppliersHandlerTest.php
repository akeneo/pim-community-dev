<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliers;
use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliersHandler;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Persistence\Query\InMemory\InMemoryGetSupplierList;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Persistence\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class GetSuppliersHandlerTest extends TestCase
{
    /** @test */
    public function itGetsSuppliers(): void
    {
        $repository = new InMemoryRepository();
        $getSupplierList = new InMemoryGetSupplierList($repository);

        $supplierIdentifier = Uuid::uuid4()->toString();
        $repository->save(Supplier::create(
            $supplierIdentifier,
            'supplier_code',
            'Supplier label'
        ));

        $sut = new GetSuppliersHandler($getSupplierList);
        $suppliers = ($sut)(new GetSuppliers());

        static::assertCount(1, $suppliers);
        static::assertSame('supplier_code', $suppliers[$supplierIdentifier]->code);
        static::assertSame('Supplier label', $suppliers[$supplierIdentifier]->label);
    }
}
