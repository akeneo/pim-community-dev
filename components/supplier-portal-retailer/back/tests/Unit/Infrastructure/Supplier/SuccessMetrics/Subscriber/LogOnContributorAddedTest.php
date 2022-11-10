<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\Supplier\SuccessMetrics\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\SuccessMetrics\Subscriber\LogOnContributorAdded;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class LogOnContributorAddedTest extends TestCase
{
    /** @test */
    public function itLogsWhenAContributorHasBeenAddedToASupplier(): void
    {
        $logger = new TestLogger();
        $sut = new LogOnContributorAdded($logger);
        $supplierIdentifier = Identifier::fromString('a3d25314-04ca-4bf9-9423-e40362d84523');

        $sut->logOnContributorAdded(
            new ContributorAdded(
                $supplierIdentifier,
                'contributor@example.com',
                'los_pollos_hermanos',
            ),
        );

        static::assertTrue($logger->hasInfo([
            'message' => 'Contributor "contributor@example.com" created.',
            'context' => [
                'data' => [
                    'identifier' => 'a3d25314-04ca-4bf9-9423-e40362d84523',
                    'metric_key' => 'contributor_added',
                    'supplier_code' => 'los_pollos_hermanos',
                ],
            ],
        ]));
    }
}
