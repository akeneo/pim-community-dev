<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\ProductsWithImage;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEvaluationRatesByProductsAndCriterionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsWithImageQuery implements ComputeProductsKeyIndicator
{
    public function __construct(
        private GetEvaluationRatesByProductsAndCriterionQueryInterface $getEvaluationRatesByProductAndCriterionQuery
    ) {
    }

    public function getCode(): KeyIndicatorCode
    {
        return new KeyIndicatorCode(ProductsWithImage::CODE);
    }

    public function compute(ProductEntityIdCollection $entityIdCollection): array
    {
        $productsWithImageRates = $this->getEvaluationRatesByProductAndCriterionQuery->execute(
            $entityIdCollection,
            new CriterionCode(EvaluateImageEnrichment::CRITERION_CODE)
        );

        $productsWithImage = [];
        foreach ($productsWithImageRates as $productId => $ratesByChannelLocale) {
            foreach ($ratesByChannelLocale as $channel => $localesRates) {
                foreach ($localesRates as $locale => $rate) {
                    $productsWithImage[$productId][$channel][$locale] = $rate === 100;
                }
            }
        }

        return $productsWithImage;
    }
}
