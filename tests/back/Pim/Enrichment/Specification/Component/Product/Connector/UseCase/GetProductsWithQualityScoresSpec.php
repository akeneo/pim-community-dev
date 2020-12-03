<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetLatestProductScoresByIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

class GetProductsWithQualityScoresSpec extends ObjectBehavior
{
    function let(
        GetLatestProductScoresByIdentifiersQueryInterface $getLatestProductScoresByIdentifiersQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $this->beConstructedWith($getLatestProductScoresByIdentifiersQuery, $dataQualityInsightsFeature);
    }

    function it_does_nothing_if_feature_flag_is_disabled(
        GetLatestProductScoresByIdentifiersQueryInterface $getLatestProductScoresByIdentifiersQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);

        $connectorProduct = $this->buildConnectorProduct('identifier_5', null);
        $productWithQualityScore = $this->buildConnectorProduct('identifier_5', new ChannelLocaleRateCollection());

        $this->fromConnectorProduct($connectorProduct)->shouldBeLike($productWithQualityScore);

        $this->fromConnectorProductList(
            new ConnectorProductList(1, [$connectorProduct])
        )->shouldBeLike(
            new ConnectorProductList(1, [$productWithQualityScore])
        );
    }

    function it_return_a_new_connector_product_with_quality_scores(
        GetLatestProductScoresByIdentifiersQueryInterface $getLatestProductScoresByIdentifiersQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $connectorProduct = $this->buildConnectorProduct('identifier_5', null);

        $qualityScores = ChannelLocaleRateCollection::fromArrayInt(['ecommerce' => ['en_US' => 15]]);
        $getLatestProductScoresByIdentifiersQuery->byProductIdentifier('identifier_5')->willReturn($qualityScores);

        $productWithQualityScore = $this->buildConnectorProduct('identifier_5', $qualityScores);

        $this->fromConnectorProduct($connectorProduct)->shouldBeLike($productWithQualityScore);
    }

    function it_return_a_list_of_connector_product_with_quality_scores(
        GetLatestProductScoresByIdentifiersQueryInterface $getLatestProductScoresByIdentifiersQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $connectorProduct1 = $this->buildConnectorProduct('pdt_5', null);
        $connectorProduct2 = $this->buildConnectorProduct('pdt_6', null);

        $qualityScores1 = ChannelLocaleRateCollection::fromArrayInt(['ecommerce' => ['en_US' => 15]]);
        $qualityScores2 = ChannelLocaleRateCollection::fromArrayInt(['print' => ['en_US' => 99]]);
        $getLatestProductScoresByIdentifiersQuery->byProductIdentifiers(['pdt_5','pdt_6'])->willReturn([
            'pdt_5' => $qualityScores1,
            'pdt_6' => $qualityScores2,
        ]);

        $this->fromConnectorProductList(
            new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2])
        )->shouldBeLike(
            new ConnectorProductList(2, [
                $this->buildConnectorProduct('pdt_5', $qualityScores1),
                $this->buildConnectorProduct('pdt_6', $qualityScores2),
            ])
        );
    }

    private function buildConnectorProduct($identifier, $qualityScore): ConnectorProduct
    {
        return new ConnectorProduct(
            5,
            $identifier,
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            [],
            new ReadValueCollection(),
            $qualityScore
        );
    }
}
