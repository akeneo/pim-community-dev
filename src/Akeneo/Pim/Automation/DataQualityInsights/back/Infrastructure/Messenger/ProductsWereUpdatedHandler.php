<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductsWereUpdated;
use Akeneo\Pim\Enrichment\Product\API\Event\ProductWasUpdated;
use Akeneo\Tool\Component\Messenger\PublicEventMessageInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageHandlerInterface;
use Akeneo\Tool\Component\Messenger\TraceableMessageInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductsWereUpdatedHandler implements TraceableMessageHandlerInterface
{
    public function __construct(
        private readonly CreateCriteriaEvaluations $createProductCriteriaEvaluations,
        private readonly EvaluateProducts $evaluateProducts,
    ) {
    }

    public function __invoke(TraceableMessageInterface $message): void
    {
        Assert::isInstanceOf($message, ProductsWereUpdated::class);

        $productUuids = [];
        /** @var ProductWasUpdated $productWasUpdated */
        foreach ($message->events as $productWasUpdated) {
            $productUuids[] = ProductUuid::fromUuid($productWasUpdated->productUuid());
        }

        $productUuids = ProductUuidCollection::fromProductUuids($productUuids);

        $this->createProductCriteriaEvaluations->createAll($productUuids);
        ($this->evaluateProducts)($productUuids);
    }
}
