<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

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

        $criterionEvaluation1 = new Write\CriterionEvaluation(new CriterionCode('completeness'), ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')), CriterionEvaluationStatus::pending());
        $criterionEvaluation2 = new Write\CriterionEvaluation(new CriterionCode('completion'), ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed')), CriterionEvaluationStatus::pending());
        $this->add($criterionEvaluation1)->add($criterionEvaluation2);

        $this->count()->shouldReturn(2);
    }
}
