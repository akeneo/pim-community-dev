<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasCreated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsWereCreatedOrUpdatedHandler
{
    public function __construct(
        private readonly CreateCriteriaEvaluations $createProductCriteriaEvaluations,
        private readonly EvaluateProducts $evaluateProducts,
    ) {
    }

    public function __invoke(ProductsWereCreatedOrUpdated $message): void
    {
        $productUuids = [];
        /** @var ProductWasCreated|ProductWasUpdated $event */
        foreach ($message->events as $event) {
            $productUuids[] = ProductUuid::fromUuid($event->productUuid);
        }

        $productUuids = ProductUuidCollection::fromProductUuids($productUuids);

        $this->createProductCriteriaEvaluations->createAll($productUuids);
        ($this->evaluateProducts)($productUuids);
    }
}
