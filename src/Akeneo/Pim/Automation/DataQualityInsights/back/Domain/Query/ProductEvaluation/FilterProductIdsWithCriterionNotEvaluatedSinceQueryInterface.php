<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface
{
    /**
     * @param ProductId[] $productIds
     * @return ProductId[]
     */
    public function execute(array $productIds, \DateTimeImmutable $evaluatedSince, CriterionCode $criterionCode): array;
}
