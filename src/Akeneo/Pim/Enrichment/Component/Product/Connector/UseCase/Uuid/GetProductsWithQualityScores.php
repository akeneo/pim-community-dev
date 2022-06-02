<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Uuid;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\Uuid\GetProductScoresQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\Uuid\ConnectorProductList;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GetProductsWithQualityScores
{
    public function __construct(
        private GetProductScoresQuery $getProductScoresQuery,
        private FeatureFlag $dataQualityInsightsFeature
    ) {
    }

    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return $product->buildWithQualityScores(new QualityScoreCollection([]));
        }

        return $product->buildWithQualityScores(
            $this->getProductScoresQuery->byProductUuid($product->uuid())
        );
    }

    public function fromConnectorProductList(ConnectorProductList $connectorProductList, ?string $channel = null, array $locales = []): ConnectorProductList
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return $this->returnProductsWithEmptyQualityScores($connectorProductList);
        }

        $productsQualityScores = $this->getProductsQualityScores($connectorProductList);

        $productsWithQualityScores = array_map(function (ConnectorProduct $product) use ($productsQualityScores, $channel, $locales) {
            if (isset($productsQualityScores[$product->uuid()->toString()])) {
                $productQualityScores = $this->filterProductQualityScores($productsQualityScores[$product->identifier()], $channel, $locales);
                return $product->buildWithQualityScores($productQualityScores);
            }

            return $product->buildWithQualityScores(new QualityScoreCollection([]));
        }, $connectorProductList->connectorProducts());

        return new ConnectorProductList($connectorProductList->totalNumberOfProducts(), $productsWithQualityScores);
    }

    private function returnProductsWithEmptyQualityScores(ConnectorProductList $connectorProductList): ConnectorProductList
    {
        return new ConnectorProductList(
            $connectorProductList->totalNumberOfProducts(),
            array_map(
                fn (ConnectorProduct $product) => $product->buildWithQualityScores(new QualityScoreCollection([])),
                $connectorProductList->connectorProducts()
            )
        );
    }

    private function getProductsQualityScores(ConnectorProductList $connectorProductList): array
    {
        $productUuids = array_map(
            fn (ConnectorProduct $connectorProduct): UuidInterface => $connectorProduct->uuid(),
            $connectorProductList->connectorProducts()
        );

        return $this->getProductScoresQuery->byProductUuids($productUuids);
    }

    private function filterProductQualityScores(QualityScoreCollection $productQualityScores, ?string $channel, array $locales): QualityScoreCollection
    {
        if (null === $channel && empty($locales)) {
            return $productQualityScores;
        }

        $filteredQualityScores = [];
        foreach ($productQualityScores->qualityScores as $scoreChannel => $scoresLocales) {
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
