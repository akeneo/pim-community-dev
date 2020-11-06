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

class AsynchronousCriterionEvaluationsFilterIterator extends \FilterIterator
{
    private const ASYNCHRONOUS_CRITERION_CODES = [
    ];

    public function accept(): bool
    {
        $criterionEvaluation = $this->getInnerIterator()->current();

        return in_array(strval($criterionEvaluation->getCriterionCode()), self::ASYNCHRONOUS_CRITERION_CODES);
    }
}
