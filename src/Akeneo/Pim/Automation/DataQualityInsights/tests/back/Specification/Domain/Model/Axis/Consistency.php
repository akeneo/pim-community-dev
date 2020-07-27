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

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Domain\Model\Axis;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class Consistency implements Axis
{
    public function getCode(): AxisCode
    {
        return new AxisCode('consistency');
    }

    public function getCriteriaCodes(): array
    {
        return [
            new CriterionCode('consistency_spelling'),
            new CriterionCode('consistency_textarea_lowercase_words'),
            new CriterionCode('consistency_textarea_uppercase_words'),
            new CriterionCode('consistency_attribute_spelling'),
            new CriterionCode('consistency_attribute_option_spelling'),
        ];
    }

    public function getCriterionCoefficient(CriterionCode $criterionCode): int
    {
        // TODO: Implement getCriterionCoefficient() method.
    }

}
