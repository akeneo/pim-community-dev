<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReadCriteriaRegistry
{
    private array $criteriaCodes = [];

    public function register(EvaluateCriterionInterface $criterionEvaluationService): void
    {
        $this->criteriaCodes[strval($criterionEvaluationService->getCode())] = $criterionEvaluationService->getCode();
    }

    /**
     * @return array<CriterionCode>
     */
    public function getCodes(): array
    {
        return array_values($this->criteriaCodes);
    }
}
