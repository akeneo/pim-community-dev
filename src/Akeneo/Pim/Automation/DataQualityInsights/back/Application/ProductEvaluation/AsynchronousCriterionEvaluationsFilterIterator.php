<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsynchronousCriterionEvaluationsFilterIterator extends \FilterIterator
{
    private const ASYNCHRONOUS_CRITERION_CODES = [
    ];

    public function accept()
    {
        $criterionEvaluation = $this->getInnerIterator()->current();

        return in_array(strval($criterionEvaluation->getCriterionCode()), self::ASYNCHRONOUS_CRITERION_CODES);
    }
}
