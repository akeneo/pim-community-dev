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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetDescendantVariantProductIdsQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetIgnoredProductTitleSuggestionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetProductTitleSuggestion
{
    private $getLatestCriteriaEvaluationsByProductIdQuery;

    private $getIgnoredProductTitleSuggestionQuery;

    private $getDescendantVariantProductIdsQuery;

    public function __construct(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery,
        GetDescendantVariantProductIdsQueryInterface $getDescendantVariantProductIdsQuery
    ) {
        $this->getLatestCriteriaEvaluationsByProductIdQuery = $getLatestCriteriaEvaluationsByProductIdQuery;
        $this->getIgnoredProductTitleSuggestionQuery = $getIgnoredProductTitleSuggestionQuery;
        $this->getDescendantVariantProductIdsQuery = $getDescendantVariantProductIdsQuery;
    }

    public function get(ProductId $productId, ChannelCode $channel, LocaleCode $locale, string $productType): ?string
    {
        if ($productType === 'product_model') {
            $variantProductIds = $this->getDescendantVariantProductIdsQuery->fromProductModelIds([strval($productId)]);
            if (empty($variantProductIds)) {
                return null;
            }

            return $this->getTitleSuggestionForAProduct(new ProductId((int) current($variantProductIds)), $channel, $locale);
        }

        return $this->getTitleSuggestionForAProduct($productId, $channel, $locale);
    }

    private function getTitleSuggestionForAProduct(ProductId $productId, ChannelCode $channel, LocaleCode $locale)
    {
        $evaluation = $this->getLatestCriteriaEvaluationsByProductIdQuery->execute($productId);
        $criterionEvaluation = $evaluation->get(new CriterionCode(EvaluateTitleFormatting::CRITERION_CODE));

        if ($criterionEvaluation === null) {
            return null;
        }

        $criterionEvaluationResult = $criterionEvaluation->getResult();

        if ($criterionEvaluationResult === null) {
            return null;
        }

        $data = $criterionEvaluationResult->getData();
        $titleSuggestion = $data['suggestions'][strval($channel)][strval($locale)] ?? null;
        $ignoredTitleSuggestion = $this->getIgnoredProductTitleSuggestionQuery->execute($productId, $channel, $locale);

        if ($titleSuggestion === null || $titleSuggestion === $ignoredTitleSuggestion) {
            return null;
        }

        return $titleSuggestion;
    }
}
