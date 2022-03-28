<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductModelsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\BulkUpdateProductsInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class BulkUpdateProductModelsIndexSubscriber implements EventSubscriberInterface
{
    public function __construct(private BulkUpdateProductsInterface $bulkUpdateProductModelsIndex)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ProductModelsEvaluated::class => 'bulkUpdateProductModelsIndex',
        ];
    }

    public function bulkUpdateProductModelsIndex(ProductModelsEvaluated $event): void
    {
        ($this->bulkUpdateProductModelsIndex)($event->getProductModelIds());
    }
}
