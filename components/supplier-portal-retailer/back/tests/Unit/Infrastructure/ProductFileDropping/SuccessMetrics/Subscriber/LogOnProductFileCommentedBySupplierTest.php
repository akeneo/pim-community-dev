<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\SuccessMetrics\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileCommentedBySupplier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\SuccessMetrics\Subscriber\LogOnProductFileCommentedBySupplier;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class LogOnProductFileCommentedBySupplierTest extends TestCase
{
    /** @test */
    public function itLogsWhenAProductFileHasBeenCommentedByASupplier(): void
    {
        $logger = new TestLogger();
        $sut = new LogOnProductFileCommentedBySupplier($logger);

        $sut->logOnProductFileCommentedBySupplier(
            new ProductFileCommentedBySupplier(
                'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                'Here is a comment',
                'jimmy@supplier.com',
            ),
        );

        static::assertTrue($logger->hasInfo([
            'message' => 'Contributor "jimmy@supplier.com" commented a product file.',
            'context' => [
                'data' => [
                    'identifier' => 'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                    'content' => 'Here is a comment',
                    'author_email' => 'jimmy@supplier.com',
                    'metric_key' => 'contributor_product_file_commented',
                ],
            ],
        ]));
    }
}
