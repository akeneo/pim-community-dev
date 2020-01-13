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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class EvaluateCompletenessOfAllAttributesSpec extends ObjectBehavior
{
    public function let(
        CalculateProductCompletenessInterface $completenessCalculator
    ) {
        $this->beConstructedWith($completenessCalculator);
    }

    public function it_evaluates_completeness_of_all_attributes(
        $completenessCalculator
    ) {
        $rates = new CriterionRateCollection();
        $criterionEvaluation = new CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('criterion1'),
            new ProductId(1),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending()
        );

        $expectedEvaluationResult = new CriterionEvaluationResult($rates, []);

        $completenessCalculator
            ->calculate(Argument::type(ProductId::class))
            ->willReturn($expectedEvaluationResult);

        $this
            ->evaluate($criterionEvaluation)
            ->shouldBeLike($expectedEvaluationResult);
    }
}
