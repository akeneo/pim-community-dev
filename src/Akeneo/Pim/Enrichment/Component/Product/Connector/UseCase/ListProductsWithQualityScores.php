<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

final class ListProductsWithQualityScores
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

    public function fromConnectorProducts(ConnectorProductList $connectorProductList): ConnectorProductList
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
