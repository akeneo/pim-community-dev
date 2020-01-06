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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;

final class CriterionEvaluationCollection implements \IteratorAggregate, \Countable
{
    /** @var CriterionEvaluation[] */
    private $criteriaEvaluations;

    public function __construct()
    {
        $this->criteriaEvaluations = [];
    }

    public function add(CriterionEvaluation $criterionEvaluation): self
    {
        $this->criteriaEvaluations[] = $criterionEvaluation;

        return $this;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->criteriaEvaluations);
    }

    public function count(): int
    {
        return count($this->criteriaEvaluations);
    }
}
