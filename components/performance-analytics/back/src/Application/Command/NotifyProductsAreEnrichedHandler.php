<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Application\Command;

use Akeneo\PerformanceAnalytics\Application\LogContext;
use Akeneo\PerformanceAnalytics\Domain\MessageQueue;
use Akeneo\PerformanceAnalytics\Domain\Product\GetProducts;
use Akeneo\PerformanceAnalytics\Domain\Product\ProductsWereEnrichedMessage;
use Akeneo\PerformanceAnalytics\Domain\Product\ProductWasEnriched;
use Psr\Log\LoggerInterface;

class NotifyProductsAreEnrichedHandler
{
    public function __construct(
        private MessageQueue $messageQueue,
        private GetProducts $getProducts,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(NotifyProductsAreEnriched $notifyProductsAreEnriched): void
    {
        $products = $this->getProducts->byUuids($notifyProductsAreEnriched->getProductUuids());

        $productWasEnrichedList = [];
        foreach ($notifyProductsAreEnriched->getProductsAreEnriched() as $productIsEnriched) {
            $uuidAsString = $productIsEnriched->productUuid()->toString();

            if (!array_key_exists($uuidAsString, $products)) {
                $this->logger->warning('Product not found while notifying products are enriched', LogContext::build(['uuid' => $uuidAsString]));
                continue;
            }

            $product = $products[$uuidAsString];

            $productWasEnrichedList[] = ProductWasEnriched::fromProperties(
                $product,
                $productIsEnriched->channelsLocales(),
                $productIsEnriched->enrichedAt(),
            );
        }

        $this->messageQueue->publish(
            ProductsWereEnrichedMessage::fromCollection($productWasEnrichedList)
        );
    }
}
