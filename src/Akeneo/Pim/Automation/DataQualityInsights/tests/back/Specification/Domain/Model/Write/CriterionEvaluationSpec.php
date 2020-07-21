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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionApplicability;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class CriterionEvaluationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(new CriterionCode('test'), new ProductId(42), CriterionEvaluationStatus::pending());
    }

    public function it_starts_and_ends_an_evaluation()
    {
        $this->getStatus()->shouldBeLike(CriterionEvaluationStatus::pending());

        $this->start();
        $this->getStatus()->shouldBeLike(CriterionEvaluationStatus::inProgress());
        $this->getEvaluatedAt()->shouldBeNull();

        $result = new CriterionEvaluationResult();
        $this->end($result);
        $this->getStatus()->shouldBeLike(CriterionEvaluationStatus::done());
        $this->getResult()->shouldBe($result);
        $this->getEvaluatedAt()->shouldNotBeNull();
    }

    public function it_changes_it_status_to_done_if_it_is_not_applicable()
    {
        $result = new CriterionEvaluationResult();
        $this->applicabilityEvaluated(new CriterionApplicability($result, false));
        $this->getStatus()->shouldBeLike(CriterionEvaluationStatus::done());
        $this->getResult()->shouldBe($result);
        $this->getEvaluatedAt()->shouldNotBeNull();
    }

    public function it_changes_it_status_to_pending_if_it_is_applicable()
    {
        $result = new CriterionEvaluationResult();
        $this->applicabilityEvaluated(new CriterionApplicability($result, true));
        $this->getStatus()->shouldBeLike(CriterionEvaluationStatus::pending());
        $this->getResult()->shouldBe($result);
        $this->getEvaluatedAt()->shouldBeNull();
    }
}
