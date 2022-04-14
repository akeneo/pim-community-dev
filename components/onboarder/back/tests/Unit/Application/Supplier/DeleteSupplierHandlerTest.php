<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Application\Supplier\DeleteSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\DeleteSupplierHandler;
use PHPUnit\Framework\TestCase;

final class DeleteSupplierHandlerTest extends TestCase
{
    /** @test */
    public function itDeletesASupplier(): void
    {
        $identifier = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
        );

        $spy = $this->createMock(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\Repository::class);
        $spy->expects($this->once())->method('delete')->with($identifier);

        $sut = new DeleteSupplierHandler($spy);
        ($sut)(new DeleteSupplier(
            (string) $identifier,
        ));
    }
}
