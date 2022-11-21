<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
