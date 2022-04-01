<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Query\InMemory;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierExport;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryGetSupplierExportTest extends TestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoSupplierToExport(): void
    {
        $sut = new InMemoryGetSupplierExport(new InMemoryRepository());

        static::assertCount(0, $sut());
    }

    /** @test */
    public function itGetsSupplierExport(): void
    {
        $supplierRepository = new InMemoryRepository();
        $sut = new InMemoryGetSupplierExport($supplierRepository);

        $supplierRepository->save(
            Supplier::create(
                'ca8baefd-0e05-4683-be48-6b9ff87e4cbc',
                'supplier1',
                'Supplier1',
                ['foo1@foo.bar', 'foo2@foo.bar']
            )
        );

        $supplierRepository->save(
            Supplier::create(
                'c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1',
                'supplier2',
                'Supplier2',
                []
            )
        );

        $suppliers = ($sut)();

        static::assertCount(2, $suppliers);
        static::assertSame('supplier1', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->code);
        static::assertSame('Supplier1', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->label);
        static::assertCount(2, $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->contributors);
        static::assertSame('foo1@foo.bar', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->contributors[0]);
        static::assertSame('foo2@foo.bar', $suppliers['ca8baefd-0e05-4683-be48-6b9ff87e4cbc']->contributors[1]);
        static::assertSame('supplier2', $suppliers['c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1']->code);
        static::assertSame('Supplier2', $suppliers['c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1']->label);
        static::assertCount(0, $suppliers['c6a23965-7e5d-4cf4-bdaa-41ddfe7481b1']->contributors);
    }
}
