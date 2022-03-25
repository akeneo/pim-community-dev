<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AddProductScoreProperty implements AddAdditionalProductProperties
{
    public function __construct(private GetProductScoresQueryInterface $getProductScores)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $allScores = $this->fetchAllScores($rows);

        $channel = new ChannelCode($queryParameters->channelCode());
        $locale = new LocaleCode($queryParameters->localeCode());

        return $this->enrichRows($rows, $allScores, $channel, $locale);
    }

    /**
     * @param Row[] $rows
     * @return ChannelLocaleRateCollection[]
     */
    private function fetchAllScores(array $rows): array
    {
        $productIds = [];
        foreach ($rows as $row) {
            $productIds[] = new ProductId($row->technicalId());
        }

        return $this->getProductScores->byProductIds(ProductIdCollection::fromProductIds($productIds));
    }

    /**
     * @param Row[] $rows
     * @return Row[]
     */
    private function enrichRows(array $rows, array $allScores, ChannelCode $channel, LocaleCode $locale): array
    {
        $enrichedRows = [];
        foreach ($rows as $row) {
            $scoreValue = $this->retrieveScore($row->technicalId(), $allScores, $channel, $locale);
            $property = new AdditionalProperty('data_quality_insights_score', $scoreValue);
            $enrichedRows[] = $row->addAdditionalProperty($property);
        }
        return $enrichedRows;
    }

    private function retrieveScore(int $productId, array $productScores, ChannelCode $channel, LocaleCode $locale): string
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
