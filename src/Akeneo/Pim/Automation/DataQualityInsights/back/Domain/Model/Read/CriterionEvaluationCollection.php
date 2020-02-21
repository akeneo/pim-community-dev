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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

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
        $this->criteriaEvaluations[strval($criterionEvaluation->getCriterionCode())] = $criterionEvaluation;

        return $this;
    }

    public function get(CriterionCode $criterionCode): ?CriterionEvaluation
    {
        return $this->criteriaEvaluations[strval($criterionCode)] ?? null;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->criteriaEvaluations);
    }

    public function count(): int
    {
        return count($this->criteriaEvaluations);
    }

    public function filterByAxis(Axis $axis): self
    {
        $filteredCollection = new self();

        foreach ($this->criteriaEvaluations as $criterionEvaluation) {
            if (in_array($criterionEvaluation->getCriterionCode(), $axis->getCriteriaCodes())) {
                $filteredCollection->add($criterionEvaluation);
            }
        }

        return $filteredCollection;
    }
}
