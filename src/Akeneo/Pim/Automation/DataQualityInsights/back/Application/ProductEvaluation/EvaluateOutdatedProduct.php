<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductUuidFactory;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Evaluate all the pending criteria of a product if it has an outdated evaluation.
 */
class EvaluateOutdatedProduct
{
    public function __construct(
        private HasUpToDateEvaluationQueryInterface $hasUpToDateEvaluationQuery,
        private EvaluateProducts $evaluateProducts,
        private ProductUuidFactory $factory
    ) {
    }

    public function __invoke(ProductUuid $productUuid): void
    {
        if (false === $this->hasUpToDateEvaluationQuery->forEntityId($productUuid)) {
            ($this->evaluateProducts)($this->factory->createCollection([(string) $productUuid]));
        }
    }
}
