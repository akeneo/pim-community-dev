<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Retailer\Application\Supplier\DeleteSupplier;
use Akeneo\OnboarderSerenity\Retailer\Application\Supplier\DeleteSupplierHandler;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class DeleteSupplierHandlerTest extends TestCase
{
    /** @test */
    public function itDeletesASupplier(): void
    {
        $identifier = Identifier::fromString(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
        );

        $spy = $this->createMock(Repository::class);
        $spy->expects($this->once())->method('delete')->with($identifier);

        $sut = new DeleteSupplierHandler($spy, new NullLogger());
        ($sut)(new DeleteSupplier(
            (string) $identifier,
        ));
    }
}
