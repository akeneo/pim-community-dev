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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class CreateProductsCriteriaEvaluationsSpec extends ObjectBehavior
{
    public function it_creates_criteria(
        CriteriaEvaluationRegistry $criterionEvaluationRegistry,
        CriterionEvaluationRepositoryInterface $criterionEvaluationRepository,
        Clock $clock
    ) {
        $currentDateTime = new \DateTimeImmutable();
        $clock->getCurrentTime()->willReturn($currentDateTime);
        $this->beConstructedWith($criterionEvaluationRegistry, $criterionEvaluationRepository, $clock);

        $productId = new ProductId(42);

        $criterionEvaluationRegistry->getCriterionCodes()->willReturn([new CriterionCode('criterion1'), new CriterionCode('criterion2')]);

        $criterionEvaluationRepository->create(Argument::that(function (CriterionEvaluationCollection $collection) {
            return $collection->count() === 2;
        }))->shouldBeCalled();

        $this->create([$productId]);
    }
}
