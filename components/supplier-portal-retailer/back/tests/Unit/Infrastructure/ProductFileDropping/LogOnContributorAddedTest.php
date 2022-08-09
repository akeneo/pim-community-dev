<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Subscriber\LogOnContributorAdded;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LogOnContributorAddedTest extends TestCase
{
    /** @test */
    public function itLogsWhenAContributorHasBeenAddedToASupplier(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $sut = new LogOnContributorAdded($logger);

        $logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Contributor "contributor@example.com" created.',
                [
                    'data' => [
                        'identifier' => 'a3d25314-04ca-4bf9-9423-e40362d84523',
                        'metric_key' => 'contributor_added',
                    ],
                ],
            )
        ;

        $sut->logOnContributorAdded(
            new ContributorAdded(
                Identifier::fromString('a3d25314-04ca-4bf9-9423-e40362d84523'),
                'contributor@example.com',
            ),
        );
    }
}
