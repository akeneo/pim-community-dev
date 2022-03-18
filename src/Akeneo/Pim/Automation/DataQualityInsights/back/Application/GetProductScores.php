<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductScores
{
    private GetProductScoresQueryInterface $getProductScoresQuery;

    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

    public function __construct(GetProductScoresQueryInterface $getProductScoresQuery, GetLocalesByChannelQueryInterface $getLocalesByChannelQuery)
    {
        $this->getProductScoresQuery = $getProductScoresQuery;
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
    }

    public function get(ProductId $productId): array
    {
        $productScores = $this->getProductScoresQuery->byProductId($productId);

        $formattedProductScores = [];
        foreach ($this->getLocalesByChannelQuery->getChannelLocaleCollection() as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $score = $productScores->getByChannelAndLocale($channelCode, $localeCode);
                $formattedProductScores[strval($channelCode)][strval($localeCode)] =
                    $score !== null ? $score->toLetter() : null;
            }
        }

        return $formattedProductScores;
    }
}
