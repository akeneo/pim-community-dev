<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\Supplier\Write\Model\Supplier;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Contributor;
use PHPUnit\Framework\TestCase;

class ContributorTest extends TestCase
{
    /** @test */
    public function itCreatesAContributor(): void
    {
        $contributor = Contributor::fromEmail('foo@foo.com');

        static::assertInstanceOf(Contributor::class, $contributor);
        static::assertSame('foo@foo.com', $contributor->email());
    }
}
