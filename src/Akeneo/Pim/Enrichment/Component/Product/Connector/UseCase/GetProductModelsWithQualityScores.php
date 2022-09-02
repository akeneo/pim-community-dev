<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelsWithQualityScores implements GetProductModelsWithQualityScoresInterface
{
    public function __construct(
        private GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        private FeatureFlag $dataQualityInsightsFeature
    ) {
    }

    public function fromConnectorProductModel(ConnectorProductModel $productModel): ConnectorProductModel
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return $productModel->buildWithQualityScores(new QualityScoreCollection([]));
        }

        return $productModel->buildWithQualityScores(
            $this->getProductModelScoresQuery->byProductModelCode($productModel->code())
        );
    }

    public function fromConnectorProductModelList(ConnectorProductModelList $connectorProductModelList, ?string $channel = null, array $locales = []): ConnectorProductModelList
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return $this->returnProductModelsWithEmptyQualityScores($connectorProductModelList);
        }

        $productModelsQualityScores = $this->getProductsQualityScores($connectorProductModelList);

        $productModelsWithQualityScores = array_map(function (ConnectorProductModel $productModel) use ($productModelsQualityScores, $channel, $locales) {
            if (!isset($productModelsQualityScores[$productModel->code()])) {
                return $productModel->buildWithQualityScores(new QualityScoreCollection([]));
            }

            $productQualityScores = $this->filterProductModelQualityScores($productModelsQualityScores[$productModel->code()], $channel, $locales);
            return $productModel->buildWithQualityScores($productQualityScores);
        }, $connectorProductModelList->connectorProductModels());

        return new ConnectorProductModelList($connectorProductModelList->totalNumberOfProductModels(), $productModelsWithQualityScores);
    }

    private function returnProductModelsWithEmptyQualityScores(ConnectorProductModelList $connectorProductModelList): ConnectorProductModelList
    {
        return new ConnectorProductModelList(
            $connectorProductModelList->totalNumberOfProductModels(),
            array_map(
                fn (ConnectorProductModel $productModel) => $productModel->buildWithQualityScores(new QualityScoreCollection([])),
                $connectorProductModelList->connectorProductModels()
            )
        );
    }

    private function getProductsQualityScores(ConnectorProductModelList $connectorProductModelList): array
    {
        $productModelCodes = array_map(
            fn (ConnectorProductModel $connectorProductModel) => $connectorProductModel->code(),
            $connectorProductModelList->connectorProductModels()
        );

        return $this->getProductModelScoresQuery->byProductModelCodes($productModelCodes);
    }

    private function filterProductModelQualityScores(QualityScoreCollection $productModelQualityScores, ?string $channel, array $locales): QualityScoreCollection
    {
        if (null === $channel && empty($locales)) {
            return $productModelQualityScores;
        }

        $filteredQualityScores = [];
        foreach ($productModelQualityScores->qualityScores as $scoreChannel => $scoresLocales) {
            if ($channel !== null && $channel !== $scoreChannel) {
                continue;
            }
            foreach ($scoresLocales as $scoreLocale => $score) {
                if (empty($locales) || in_array($scoreLocale, $locales)) {
                    $filteredQualityScores[$scoreChannel][$scoreLocale] = $score;
                }
            }
        }

        return new QualityScoreCollection($filteredQualityScores);
    }
}
