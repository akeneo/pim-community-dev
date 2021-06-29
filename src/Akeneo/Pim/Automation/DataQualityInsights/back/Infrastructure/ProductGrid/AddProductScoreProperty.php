<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddProductScoreProperty implements AddAdditionalProductProperties
{
    private GetLatestProductScoresQueryInterface $getProductScores;

    public function __construct(GetLatestProductScoresQueryInterface $getProductScores)
    {
        $this->getProductScores = $getProductScores;
    }

    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $productIds = [];
        foreach ($rows as $row) {
            $productIds[] = new ProductId($row->technicalId());
        }

        $productScores = $this->getProductScores->byProductIds($productIds);
        $channel = new ChannelCode($queryParameters->channelCode());
        $locale = new LocaleCode($queryParameters->localeCode());

        $rowsWithAdditionalProperty = [];
        foreach ($rows as $row) {
            $scoreValue = $this->retrieveProductScore($row->technicalId(), $productScores, $channel, $locale);
            $property = new AdditionalProperty('data_quality_insights_score', $scoreValue);
            $rowsWithAdditionalProperty[] = $row->addAdditionalProperty($property);
        }

        return $rowsWithAdditionalProperty;
    }

    private function retrieveProductScore(int $productId, array $productScores, ChannelCode $channel, LocaleCode $locale): string
    {
        if (isset($productScores[$productId])) {
            $score = $productScores[$productId]->getByChannelAndLocale($channel, $locale);
            if ($score !== null) {
                return $score->toLetter();
            }
        }

        return 'N/A';
    }
}
