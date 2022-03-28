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
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddScoresToProductAndProductModelRows
{
    /**
     * @param Row[] $rows
     * @param ChannelLocaleRateCollection[] $scores
     */
    public function __invoke($fetchProductAndProductModelRowsParameters, array $rows, array $scores): array
    {
        $channel = new ChannelCode($fetchProductAndProductModelRowsParameters->channelCode());
        $locale = new LocaleCode($fetchProductAndProductModelRowsParameters->localeCode());

        $enrichedRows = [];
        foreach ($rows as $row) {
            $scoreValue = $this->retrieveScore($row->technicalId(), $scores, $channel, $locale);
            $property = new AdditionalProperty('data_quality_insights_score', $scoreValue);
            $enrichedRows[] = $row->addAdditionalProperty($property);
        }

        return $enrichedRows;
    }

    private function retrieveScore(int $technicalId, array $scores, ChannelCode $channel, LocaleCode $locale): string
    {
        if (isset($scores[$technicalId])) {
            $score = $scores[$technicalId]->getByChannelAndLocale($channel, $locale);
            if ($score !== null) {
                return $score->toLetter();
            }
        }

        return 'N/A';
    }
}
