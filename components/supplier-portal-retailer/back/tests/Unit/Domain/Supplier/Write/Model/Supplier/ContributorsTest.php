<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\Supplier\Write\Model\Supplier;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Contributors;
use PHPUnit\Framework\TestCase;

class ContributorsTest extends TestCase
{
    /** @test */
    public function itCreatesACollectionOfContributors(): void
    {
        $contributors = Contributors::fromEmails(['zoo@zoo.com', 'moo@moo.com']);

        static::assertInstanceOf(Contributors::class, $contributors);
        static::assertSame([
            ['email' => 'zoo@zoo.com'],
            ['email' => 'moo@moo.com'],
        ], $contributors->toArray());
        static::assertCount(2, $contributors);
    }

    /** @test */
    public function itComputesCreatedContributorEmails(): void
    {
        $currentContributorEmails = [
            'foo@foo.foo',
        ];

        $newContributorEmails = [
            'foo@foo.foo',
            'bar@bar.bar',
        ];

        $contributors = Contributors::fromEmails($currentContributorEmails);

        static::assertSame(['bar@bar.bar'], $contributors->computeCreatedContributorEmails($newContributorEmails));
    }

    /** @test */
    public function itComputesDeletedContributorEmails(): void
    {
        $currentContributorEmails = [
            'foo@foo.foo',
            'bar@bar.bar',
        ];

        $newContributorEmails = [
            'foo@foo.foo',
        ];

        $contributors = Contributors::fromEmails($currentContributorEmails);

        static::assertSame(['bar@bar.bar'], $contributors->computeDeletedContributorEmails($newContributorEmails));
    }
}
