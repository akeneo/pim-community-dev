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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Textarea\EvaluateUppercaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Consistency;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

final class CriterionEvaluationCollectionSpec extends ObjectBehavior
{
    public function it_is_a_criterion_evaluation_collection()
    {
        $this->shouldHaveType(Read\CriterionEvaluationCollection::class);
    }

    public function it_gives_a_criterion_evaluation_by_its_code()
    {
        $completenessEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('completeness_of_required_attributes'),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null,
            null,
            null
        );

        $spellingEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('consistency_textarea_uppercase_words'),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null,
            null,
            null
        );

        $this
            ->add($completenessEvaluation)
            ->add($spellingEvaluation);

        $this->get(new CriterionCode('completeness_of_required_attributes'))->shouldReturn($completenessEvaluation);
    }

    public function it_gives_the_count_of_the_criteria_evaluations()
    {
        $completenessEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('completeness_of_required_attributes'),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null,
            null,
            null
        );

        $spellingEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('consistency_textarea_uppercase_words'),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null,
            null,
            null
        );

        $this
            ->add($completenessEvaluation)
            ->add($spellingEvaluation);

        $this->count()->shouldReturn(2);
    }

    public function it_filters_criteria_evaluations_by_axis()
    {
        $completenessEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null,
            null,
            null
        );

        $spellingEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode(EvaluateSpelling::CRITERION_CODE),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null,
            null,
            null
        );

        $upperCaseEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode(EvaluateUppercaseWords::CRITERION_CODE),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null,
            null,
            null
        );


        $this->add($spellingEvaluation)
            ->add($completenessEvaluation)
            ->add($upperCaseEvaluation);

        $filteredCriteriaEvaluations = $this->filterByAxis(new Consistency())->getWrappedObject();

        $expectedCriteriaEvaluations = [
            EvaluateSpelling::CRITERION_CODE => $spellingEvaluation,
            EvaluateUppercaseWords::CRITERION_CODE => $upperCaseEvaluation,
        ];

        Assert::count($filteredCriteriaEvaluations, 2);
        Assert::eq($expectedCriteriaEvaluations, iterator_to_array($filteredCriteriaEvaluations));
    }

    public function it_gives_the_rates_of_a_given_criterion()
    {
        $completenessResult = new Read\CriterionEvaluationResult(
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(100)),
            (new CriterionEvaluationResultStatusCollection())
                ->add(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done()),
            []
        );

        $completenessEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('completeness_of_required_attributes'),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            $completenessResult,
            null,
            null
        );

        $spellingEvaluation = new Read\CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode('consistency_textarea_uppercase_words'),
            new ProductId(42),
            new \DateTimeImmutable(),
            CriterionEvaluationStatus::pending(),
            null,
            null,
            null
        );

        $this
            ->add($completenessEvaluation)
            ->add($spellingEvaluation);

        $this->getCriterionRates(new CriterionCode('foo'))->shouldReturn(null);
        $this->getCriterionRates(new CriterionCode('consistency_textarea_uppercase_words'))->shouldReturn(null);
        $this->getCriterionRates(new CriterionCode('completeness_of_required_attributes'))->shouldReturn($completenessResult->getRates());
    }
}
