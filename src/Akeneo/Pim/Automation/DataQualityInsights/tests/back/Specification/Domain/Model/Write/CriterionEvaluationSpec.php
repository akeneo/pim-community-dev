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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

final class CriterionEvaluationSpec extends ObjectBehavior
{
    public function it_starts_and_ends_an_evaluation()
    {
        $this->beConstructedWith(new CriterionEvaluationId(), new CriterionCode('test'), new ProductId(42), new \DateTimeImmutable(), CriterionEvaluationStatus::pending());

        $this->getStatus()->shouldBeLike(CriterionEvaluationStatus::pending());
        $this->getStartedAt()->shouldBeNull();
        $this->getEndedAt()->shouldBeNull();

        $this->start();
        $this->getStatus()->shouldBeLike(CriterionEvaluationStatus::inProgress());
        $this->getStartedAt()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
        $this->getEndedAt()->shouldBeNull();

        $this->end(new CriterionEvaluationResult());
        $this->getStatus()->shouldBeLike(CriterionEvaluationStatus::done());
        $this->getStartedAt()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
        $this->getEndedAt()->shouldBeAnInstanceOf(\DateTimeImmutable::class);
    }
}
