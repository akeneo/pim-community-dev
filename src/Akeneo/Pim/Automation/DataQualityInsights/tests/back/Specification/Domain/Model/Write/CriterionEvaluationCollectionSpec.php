<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
