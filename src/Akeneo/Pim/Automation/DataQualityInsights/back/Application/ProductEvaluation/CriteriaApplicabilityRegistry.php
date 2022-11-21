<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaApplicabilityRegistry
{
    private $criterionApplicabilityServices;

    public function __construct(iterable $criterionApplicabilityServices)
    {
        $this->criterionApplicabilityServices = [];
        foreach ($criterionApplicabilityServices as $criterionApplicabilityService) {
            if ($criterionApplicabilityService instanceof EvaluateCriterionApplicabilityInterface) {
                $this->criterionApplicabilityServices[strval($criterionApplicabilityService->getCode())] = $criterionApplicabilityService;
            }
        }
    }

    public function get(CriterionCode $code): ?EvaluateCriterionApplicabilityInterface
    {
        return $this->criterionApplicabilityServices[strval($code)] ?? null;
    }

    /**
     * @return CriterionCode[]
     */
    public function getCriterionCodes(): array
    {
        return array_map(function (string $code) {
            return new CriterionCode($code);
        }, array_keys($this->criterionApplicabilityServices));
    }
}
