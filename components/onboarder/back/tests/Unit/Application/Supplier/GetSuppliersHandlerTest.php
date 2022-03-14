<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliers;
use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliersHandler;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierList;
use PHPUnit\Framework\TestCase;

final class GetSuppliersHandlerTest extends TestCase
{
    /** @test */
    public function itGetsSuppliers(): void
    {
        $spy = $this->createMock(GetSupplierList::class);
        $spy->expects($this->once())->method('__invoke')->with(1, '');

        $sut = new GetSuppliersHandler($spy);
        ($sut)(new GetSuppliers());
    }
}
