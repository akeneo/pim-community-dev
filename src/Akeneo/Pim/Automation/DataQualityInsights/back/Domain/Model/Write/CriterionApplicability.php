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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

final class CriterionApplicability
{
    /** @var CriterionEvaluationResult */
    private $evaluationResult;

    /** @var bool */
    private $isApplicable;

    public function __construct(CriterionEvaluationResult $evaluationResult, bool $isApplicable)
    {
        $this->evaluationResult = $evaluationResult;
        $this->isApplicable = $isApplicable;
    }

    public function getEvaluationResult(): CriterionEvaluationResult
    {
        return $this->evaluationResult;
    }

    /**
     * Determine if the criterion is applicable on at least one product value.
     */
    public function isApplicable(): bool
    {
        return $this->isApplicable;
    }
}
