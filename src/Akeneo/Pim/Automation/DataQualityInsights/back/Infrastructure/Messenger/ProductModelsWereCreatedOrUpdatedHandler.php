<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelsWereCreatedOrUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasCreated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasUpdated;

class ProductModelsWereCreatedOrUpdatedHandler
{
    public function __construct(
        private readonly CreateCriteriaEvaluations $createProductModelCriteriaEvaluations,
        private readonly EvaluateProductModels $evaluateProductModels,
    ) {
    }

    public function __invoke(ProductModelsWereCreatedOrUpdated $message): void
    {
        $productModelIds = [];
        /** @var ProductModelWasCreated|ProductModelWasUpdated $event */
        foreach ($message->events as $event) {
            $productModelIds[] = (string) $event->id;
        }

        $productModelIdCollection = ProductModelIdCollection::fromStrings($productModelIds);

        $this->createProductModelCriteriaEvaluations->createAll($productModelIdCollection);
        ($this->evaluateProductModels)($productModelIdCollection);
    }
}
