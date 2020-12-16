<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetPendingCriteriaEvaluationsByProductIdsQueryInterface
{
    /**
     * @param int[] $productIds
     *
     * @return Write\CriterionEvaluationCollection[]
     */
    public function execute(array $productIds): array;
}
