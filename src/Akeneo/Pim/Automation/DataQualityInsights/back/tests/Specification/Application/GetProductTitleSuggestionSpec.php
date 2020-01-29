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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetIgnoredProductTitleSuggestionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetProductTitleSuggestionSpec extends ObjectBehavior
{
    public function let(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $this->beConstructedWith($getLatestCriteriaEvaluationsByProductIdQuery, $getIgnoredProductTitleSuggestionQuery);
    }

    public function it_does_not_get_product_title_suggestion_when_product_is_not_evaluated(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $productId = new ProductId(1000);
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $rawEvaluation = new CriterionEvaluationCollection();

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId, $channel, $locale)->shouldBeLike(null);
    }

    public function it_does_not_get_product_title_suggestion_when_locale_is_not_supported(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $productId = new ProductId(1000);
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('it_IT');
        $rawEvaluation = $this->generateEvaluation($productId);

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId, $channel, $locale)->shouldBeLike(null);
    }

    public function it_does_not_get_product_title_suggestion_when_is_ignored(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $productId = new ProductId(1000);
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $rawEvaluation = $this->generateEvaluation($productId);

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);
        $getIgnoredProductTitleSuggestionQuery->execute($productId, $channel, $locale)->willReturn("My suggested title");

        $this->get($productId, $channel, $locale)->shouldBeLike(null);
    }

    public function it_gets_product_title_suggestion(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $productId = new ProductId(1000);
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $rawEvaluation = $this->generateEvaluation($productId);

        $getLatestCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId, $channel, $locale)->shouldBeLike("My suggested title");
    }

    private function generateCriterionEvaluation(ProductId $productId, string $code, string $status, CriterionRateCollection $resultRates, array $resultData)
    {
        return new CriterionEvaluation(
            new CriterionEvaluationId(),
            new CriterionCode($code),
            $productId,
            new \DateTimeImmutable(),
            new CriterionEvaluationStatus($status),
            new CriterionEvaluationResult($resultRates, $resultData),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    private function generateEvaluation(ProductId $productId): CriterionEvaluationCollection
    {
        $channelCode = new ChannelCode('ecommerce');
        $localeCode = new LocaleCode('en_US');

        $evaluateTitleFormattingRates = new CriterionRateCollection();
        $evaluateTitleFormattingRates
            ->addRate($channelCode, $localeCode, new Rate(88))
        ;
        $evaluateTitleFormattingData = [
            "attributes" => [
                "ecommerce" => [
                    "en_US" => ["name"]
                ]
            ],
            "suggestions" => [
                "ecommerce" => [
                    "en_US" => "My suggested title"
                ]
            ]
        ];

        $evaluation = new CriterionEvaluationCollection();
        $evaluation
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateTitleFormatting::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $evaluateTitleFormattingRates,
                $evaluateTitleFormattingData
            ))
        ;

        return $evaluation;
    }
}
