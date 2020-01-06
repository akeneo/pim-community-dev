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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AxisRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

interface ComputeAxisRatesInterface
{
    public function compute(Read\CriterionEvaluationCollection $criterionEvaluationCollection): AxisRateCollection;
}
