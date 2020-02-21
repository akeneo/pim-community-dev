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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;

final class AxisEvaluationCollection implements \IteratorAggregate
{
    /** @var AxisEvaluation[] */
    private $criteriaEvaluations;

    public function __construct()
    {
        $this->criteriaEvaluations = [];
    }

    public function add(AxisEvaluation $axisEvaluation): self
    {
        $this->criteriaEvaluations[strval($axisEvaluation->getAxisCode())] = $axisEvaluation;

        return $this;
    }

    public function get(AxisCode $axisCode): ?AxisEvaluation
    {
        return $this->criteriaEvaluations[strval($axisCode)] ?? null;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->criteriaEvaluations);
    }
}
