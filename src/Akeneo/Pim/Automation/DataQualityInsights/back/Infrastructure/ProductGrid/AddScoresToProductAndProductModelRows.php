<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddScoresToProductAndProductModelRows
{
    public function __construct(
        private GetQualityScoresFactory         $getQualityScoresFactory,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    /**
     * @param Row[] $rows
     * @param 'product_model'|'product' $type
     */
    public function __invoke(
        FetchProductAndProductModelRowsParameters $fetchProductAndProductModelRowsParameters,
        array                                     $rows,
        string                                    $type
    ): array {
        if (empty($rows)) {
            return [];
        }

        $productIds = [];
        foreach ($rows as $row) {
            if ($type === $row->documentType()) {
                $productIds[] = (string) $row->technicalId();
            }
        }

        $productIdCollection = $this->idFactory->createCollection($productIds);

        $scores = ($this->getQualityScoresFactory)($productIdCollection, $type);

        $channel = new ChannelCode($fetchProductAndProductModelRowsParameters->channelCode());
        $locale = new LocaleCode($fetchProductAndProductModelRowsParameters->localeCode());

        $enrichedRows = [];
        foreach ($rows as $row) {
            $scoreValue = $this->retrieveScore((string) $row->technicalId(), $scores, $channel, $locale);
            $property = new AdditionalProperty('data_quality_insights_score', $scoreValue);
            $enrichedRows[] = $row->addAdditionalProperty($property);
        }

        return $enrichedRows;
    }

    private function retrieveScore(string $technicalId, array $scores, ChannelCode $channel, LocaleCode $locale): string
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
