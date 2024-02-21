<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductModelsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\BulkUpdateProductQualityScoresInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class BulkUpdateProductModelQualityScoresIndexSubscriber implements EventSubscriberInterface
{
    public function __construct(private BulkUpdateProductQualityScoresInterface $bulkUpdateProductQualityScores)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductModelsEvaluated::class => 'bulkUpdateProductModelQualityScoresIndex',
        ];
    }

    public function bulkUpdateProductModelQualityScoresIndex(ProductModelsEvaluated $event): void
    {
        ($this->bulkUpdateProductQualityScores)($event->getProductModelIds());
    }
}
