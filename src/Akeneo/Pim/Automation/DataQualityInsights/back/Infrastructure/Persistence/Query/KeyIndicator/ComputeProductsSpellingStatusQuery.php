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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\ProductsWithPerfectSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

final class ComputeProductsSpellingStatusQuery implements ComputeProductsKeyIndicator
{
    private GetEvaluationRatesByProductsAndCriterionQueryInterface $getEvaluationRatesByProductAndCriterionQuery;

    public function __construct(GetEvaluationRatesByProductsAndCriterionQueryInterface $getEvaluationRatesByProductAndCriterionQuery)
    {
        $this->getEvaluationRatesByProductAndCriterionQuery = $getEvaluationRatesByProductAndCriterionQuery;
    }

    public function getCode(): KeyIndicatorCode
    {
        return new KeyIndicatorCode(ProductsWithPerfectSpelling::CODE);
    }

    public function compute(ProductEntityIdCollection $productIdCollection): array
    {
        $productsSpellingRates = $this->getEvaluationRatesByProductAndCriterionQuery->execute(
            $productIdCollection,
            new CriterionCode(EvaluateSpelling::CRITERION_CODE)
        );

        $productsSpellingStatus = [];
        foreach ($productsSpellingRates as $productId => $ratesByChannelLocale) {
            foreach ($ratesByChannelLocale as $channel => $localesRates) {
                foreach ($localesRates as $locale => $rate) {
                    $productsSpellingStatus[$productId][$channel][$locale] = $rate === 100;
                }
            }
        }

        return $productsSpellingStatus;
    }
}
