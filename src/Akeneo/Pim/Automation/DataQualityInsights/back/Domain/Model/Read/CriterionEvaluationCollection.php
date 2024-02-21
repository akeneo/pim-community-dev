<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;

final class CriterionEvaluationCollection implements \IteratorAggregate, \Countable
{
    /** @var CriterionEvaluation[] */
    private array $criteriaEvaluations;

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

    public function getCriterionRates(CriterionCode $criterionCode): ?ChannelLocaleRateCollection
    {
        $criterionEvaluation = $this->get($criterionCode);
        if (null === $criterionEvaluation) {
            return null;
        }

        $criterionEvaluationResult = $criterionEvaluation->getResult();
        if (null === $criterionEvaluationResult) {
            return null;
        }

        return $criterionEvaluationResult->getRates();
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->criteriaEvaluations);
    }

    public function count(): int
    {
        return count($this->criteriaEvaluations);
    }

    public function isEmpty(): bool
    {
        return empty($this->criteriaEvaluations);
    }
}
