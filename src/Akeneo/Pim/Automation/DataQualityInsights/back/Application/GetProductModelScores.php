<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelScores
{
    public function __construct(
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private GetLocalesByChannelQueryInterface   $getLocalesByChannelQuery,
        private GetScoresByCriteriaStrategy         $getScoresByCriteria,
    ) {
    }

    /**
     * Eventually returns all quality scores by channel and locale.
     * @return array{'evaluations_available':false} | array{'evaluations_available': true, 'scores': array }
     */
    public function get(ProductModelId $productModelId): array
    {
        $productScores = ($this->getScoresByCriteria)($this->getProductModelScoresQuery->byProductModelId($productModelId));

        if ($productScores->isEmpty()) {
            return ["evaluations_available" => false];
        }

        $formattedProductScores = [];
        foreach ($this->getLocalesByChannelQuery->getChannelLocaleCollection() as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $score = $productScores->getByChannelAndLocale($channelCode, $localeCode);
                $formattedProductScores[strval($channelCode)][strval($localeCode)] = $score?->toLetter();
            }
        }

        return ["evaluations_available" => true, "scores" => $formattedProductScores];
    }
}
