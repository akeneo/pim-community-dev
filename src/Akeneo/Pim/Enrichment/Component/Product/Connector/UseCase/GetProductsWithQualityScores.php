<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

final class GetProductsWithQualityScores implements GetProductsWithQualityScoresInterface
{
    private GetLatestProductScoresByIdentifiersQueryInterface $getLatestProductScoresByIdentifiersQuery;

    private FeatureFlag $dataQualityInsightsFeature;

    public function __construct(
        GetLatestProductScoresByIdentifiersQueryInterface $getLatestProductScoresByIdentifiersQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $this->getLatestProductScoresByIdentifiersQuery = $getLatestProductScoresByIdentifiersQuery;
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
    }

    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return $product->buildWithQualityScores(new ChannelLocaleRateCollection());
        }

        return $product->buildWithQualityScores(
            $this->getLatestProductScoresByIdentifiersQuery->byProductIdentifier($product->identifier())
        );
    }

    public function fromConnectorProductList(ConnectorProductList $connectorProductList): ConnectorProductList
    {
        if (!$this->dataQualityInsightsFeature->isEnabled()) {
            return $this->returnProductsWithEmptyQualityScores($connectorProductList);
        }

        $productsQualityScores = $this->getProductsQualityScores($connectorProductList);

        $productsWithQualityScores = array_map(function (ConnectorProduct $product) use ($productsQualityScores) {
            if (array_key_exists($product->identifier(), $productsQualityScores)) {
                return $product->buildWithQualityScores($productsQualityScores[$product->identifier()]);
            }

            return $product->buildWithQualityScores(new ChannelLocaleRateCollection());
        }, $connectorProductList->connectorProducts());

        return new ConnectorProductList($connectorProductList->totalNumberOfProducts(), $productsWithQualityScores);
    }

    private function returnProductsWithEmptyQualityScores(ConnectorProductList $connectorProductList): ConnectorProductList
    {
        return new ConnectorProductList(
            $connectorProductList->totalNumberOfProducts(),
            array_map(
                fn (ConnectorProduct $product) => $product->buildWithQualityScores(new ChannelLocaleRateCollection()),
                $connectorProductList->connectorProducts()
            )
        );
    }

    private function getProductsQualityScores(ConnectorProductList $connectorProductList): array
    {
        $productIdentifiers = array_map(
            fn(ConnectorProduct $connectorProduct) => $connectorProduct->identifier(),
            $connectorProductList->connectorProducts()
        );

        return $this->getLatestProductScoresByIdentifiersQuery->byProductIdentifiers($productIdentifiers);
    }
}
