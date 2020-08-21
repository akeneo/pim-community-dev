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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class CriterionEvaluationCollectionSpec extends ObjectBehavior
{
    public function it_is_iterable()
    {
        $this->shouldImplement(\IteratorAggregate::class);
    }

    public function it_is_countable()
    {
        $this->shouldImplement(\Countable::class);
    }

    public function it_adds_criterion_evaluations()
    {
        $this->count()->shouldReturn(0);

        $criterionEvaluation1 = new Write\CriterionEvaluation(new CriterionCode('completeness'), new ProductId(42), CriterionEvaluationStatus::pending());
        $criterionEvaluation2 = new Write\CriterionEvaluation(new CriterionCode('completion'), new ProductId(42), CriterionEvaluationStatus::pending());
        $this->add($criterionEvaluation1)->add($criterionEvaluation2);

        $this->count()->shouldReturn(2);
    }
}
