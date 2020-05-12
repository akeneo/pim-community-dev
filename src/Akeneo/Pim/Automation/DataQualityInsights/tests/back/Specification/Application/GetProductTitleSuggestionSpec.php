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

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetIgnoredProductTitleSuggestionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByProductIdQueryInterface;
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
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $this->beConstructedWith(
            $getCriteriaEvaluationsByProductIdQuery,
            $getIgnoredProductTitleSuggestionQuery
        );
    }

    public function it_does_not_get_product_title_suggestion_when_product_is_not_evaluated(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $productId = new ProductId(1000);
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $rawEvaluation = new Read\CriterionEvaluationCollection();

        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId, $channel, $locale, 'product')->shouldBeLike(null);
    }

    public function it_does_not_get_product_title_suggestion_when_locale_is_not_supported(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $productId = new ProductId(1000);
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('it_IT');
        $rawEvaluation = $this->generateEvaluation($productId);

        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId, $channel, $locale, 'product')->shouldBeLike(null);
    }

    public function it_does_not_get_product_title_suggestion_when_is_ignored(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $productId = new ProductId(1000);
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $rawEvaluation = $this->generateEvaluation($productId);

        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);
        $getIgnoredProductTitleSuggestionQuery->execute($productId, $channel, $locale)->willReturn("My suggested title");

        $this->get($productId, $channel, $locale, 'product')->shouldBeLike(null);
    }

    public function it_gets_product_title_suggestion(
        GetCriteriaEvaluationsByProductIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $productId = new ProductId(1000);
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
        $rawEvaluation = $this->generateEvaluation($productId);

        $getCriteriaEvaluationsByProductIdQuery->execute($productId)->willReturn($rawEvaluation);

        $this->get($productId, $channel, $locale, 'product')->shouldBeLike("My suggested title");
    }

    private function generateCriterionEvaluation(ProductId $productId, string $code, string $status, ChannelLocaleRateCollection $resultRates, array $resultData)
    {
        return new Read\CriterionEvaluation(
            new CriterionCode($code),
            $productId,
            new \DateTimeImmutable(),
            new CriterionEvaluationStatus($status),
            new Read\CriterionEvaluationResult($resultRates, new CriterionEvaluationResultStatusCollection(), $resultData)
        );
    }

    private function generateEvaluation(ProductId $productId): Read\CriterionEvaluationCollection
    {
        $channelCode = new ChannelCode('ecommerce');
        $localeCode = new LocaleCode('en_US');

        $evaluateTitleFormattingRates = new ChannelLocaleRateCollection();
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

        $evaluation = new Read\CriterionEvaluationCollection();
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
