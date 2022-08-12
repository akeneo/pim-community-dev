<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Subscriber\LogOnProductFileDownloaded;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class LogOnProductFileDownloadedTest extends TestCase
{
    /** @test */
    public function itLogsWhenAProductFileHasBeenDownloadedByARetailer(): void
    {
        $logger = new TestLogger();
        $sut = new LogOnProductFileDownloaded($logger);

        $sut->logOnProductFileDownloaded(
            new ProductFileDownloaded(
                'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                'los_pollos_hermanos',
                1,
            ),
        );

        static::assertTrue($logger->hasInfo([
            'message' => 'Product file downloaded.',
            'context' => [
                'data' => [
                    'metric_key' => 'product_file_downloaded',
                    'supplier_code' => 'los_pollos_hermanos',
                    'supplier_file_identifier' => 'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                    'user_id' => 1,
                ],
            ],
        ]));
    }
}
