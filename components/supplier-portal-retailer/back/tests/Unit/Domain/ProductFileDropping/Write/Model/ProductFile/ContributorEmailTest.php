<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Model\ProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\ContributorEmail;
use PHPUnit\Framework\TestCase;

class ContributorEmailTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateAContributorEmailIfTheFormatIsNotValid(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The contributor email must be valid.'));

        ContributorEmail::fromString('foo@');
    }

    /** @test */
    public function itCreatesAndGetsAContributorEmailIfItsValid(): void
    {
        $email = ContributorEmail::fromString('foo@foo.bar');

        static::assertInstanceOf(ContributorEmail::class, $email);
        static::assertSame('foo@foo.bar', (string) $email);
    }
}
