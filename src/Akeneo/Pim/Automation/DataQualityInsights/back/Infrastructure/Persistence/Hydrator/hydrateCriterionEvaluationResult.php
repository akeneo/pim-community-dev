<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Hydrator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultIds;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class hydrateCriterionEvaluationResult
{
    public function __construct(
        private TransformCriterionEvaluationResultIds $transformCriterionEvaluationResultIds
    ) {
    }

    public function __invoke(CriterionCode $criterionCode, string $rawResult): ?Read\CriterionEvaluationResult
    {
        $rawResult = json_decode($rawResult, true, JSON_THROW_ON_ERROR);
        $rawResult = $this->transformCriterionEvaluationResultIds->transformToCodes($criterionCode, $rawResult);

        return Read\CriterionEvaluationResult::fromArray($rawResult);
    }
}
