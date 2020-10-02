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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;

final class AxisEvaluation
{
    /** @var AxisCode */
    private $axisCode;

    /** @var ChannelLocaleRateCollection */
    private $rates;

    /** @var CriterionEvaluationCollection */
    private $criteriaEvaluations;

    public function __construct(AxisCode $axisCode, ChannelLocaleRateCollection $rates, CriterionEvaluationCollection $criteriaEvaluations)
    {
        $this->axisCode = $axisCode;
        $this->rates = $rates;
        $this->criteriaEvaluations = $criteriaEvaluations;
    }

    public function getAxisCode(): AxisCode
    {
        return $this->axisCode;
    }

    public function getRates(): ChannelLocaleRateCollection
    {
        return $this->rates;
    }

    public function getCriteriaEvaluations(): CriterionEvaluationCollection
    {
        return $this->criteriaEvaluations;
    }
}
