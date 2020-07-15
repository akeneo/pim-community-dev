<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\CriterionNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

class CriteriaEvaluationRegistry
{
    private $criterionEvaluationServices;

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
        return array_map(function (string $code) {
            return new CriterionCode($code);
        }, array_keys($this->criterionEvaluationServices));
    }
}
