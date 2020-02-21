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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;

final class CriterionEvaluationByAxisFilterIterator extends \FilterIterator
{
    /** @var Axis */
    private $axis;

    public function __construct(CriterionEvaluationCollection $criterionEvaluationCollection, Axis $axis)
    {
        parent::__construct($criterionEvaluationCollection->getIterator());

        $this->axis = $axis;
    }

    /**
     * @inheritDoc
     */
    public function accept()
    {
        $criterionEvaluation = $this->getInnerIterator()->current();

        return $criterionEvaluation instanceof CriterionEvaluation
            && in_array($criterionEvaluation->getCriterionCode(), $this->axis->getCriteriaCodes());
    }
}
