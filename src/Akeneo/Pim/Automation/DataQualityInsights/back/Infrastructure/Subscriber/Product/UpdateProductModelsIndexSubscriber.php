<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductModelsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\UpdateProductModelsIndex;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateProductModelsIndexSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdateProductModelsIndex $updateProductModelsIndex)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ProductModelsEvaluated::class => 'updateProductsIndex',
        ];
    }

    public function updateProductsIndex(ProductModelsEvaluated $event): void
    {
        $this->updateProductModelsIndex->execute($event->getProductModelIds());
    }
}
