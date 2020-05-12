<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

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
