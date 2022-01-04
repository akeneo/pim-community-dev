<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\UpdateProductsIndex;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateProductsIndexSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdateProductsIndex $updateProductsIndex)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ProductsEvaluated::class => 'updateProductsIndex',
        ];
    }

    public function updateProductsIndex(ProductsEvaluated $event): void
    {
        $this->updateProductsIndex->execute($event->getProductIds());
    }
}
