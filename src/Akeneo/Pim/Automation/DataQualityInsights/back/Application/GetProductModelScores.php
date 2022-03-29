<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelScores
{
    public function __construct(
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private GetLocalesByChannelQueryInterface  $getLocalesByChannelQuery
    ) {
    }

    public function get(ProductId $productId): array
    {
        $productScores = $this->getProductModelScoresQuery->byProductModelId($productId);

        $formattedProductScores = [];
        foreach ($this->getLocalesByChannelQuery->getChannelLocaleCollection() as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $score = $productScores->getByChannelAndLocale($channelCode, $localeCode);
                $formattedProductScores[strval($channelCode)][strval($localeCode)] = $score?->toLetter();
            }
        }

        return $formattedProductScores;
    }
}
