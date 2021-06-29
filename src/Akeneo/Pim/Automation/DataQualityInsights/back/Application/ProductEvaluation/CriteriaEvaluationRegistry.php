<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\CriterionNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaEvaluationRegistry
{
    private array $criterionEvaluationServices;

    public function __construct(iterable $criterionEvaluationServices)
    {
        $this->criterionEvaluationServices = [];
        foreach ($criterionEvaluationServices as $criterionEvaluationService) {
            if ($criterionEvaluationService instanceof EvaluateCriterionInterface) {
                $this->criterionEvaluationServices[strval($criterionEvaluationService->getCode())] = $criterionEvaluationService;
            }
        }
    }

    public function get(CriterionCode $code): EvaluateCriterionInterface
    {
        if (!array_key_exists(strval($code), $this->criterionEvaluationServices)) {
            throw new CriterionNotFoundException(sprintf('No evaluation service found for criterion "%s"', $code));
        }

        return $this->criterionEvaluationServices[strval($code)];
    }

    /**
     * @return CriterionCode[]
     */
    public function getCriterionCodes(): array
    {
        return array_values(array_map(
            fn (EvaluateCriterionInterface $evaluateCriterion) => $evaluateCriterion->getCode(),
            $this->criterionEvaluationServices
        ));
    }

    public function getCriterionCoefficient(CriterionCode $code): int
    {
        return $this->get($code)->getCoefficient();
    }
}
