<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

class GetProductsWithQualityScoresSpec extends ObjectBehavior
{
    function let(
        GetProductScoresQueryInterface $getProductScoresQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $this->beConstructedWith($getProductScoresQuery, $dataQualityInsightsFeature);
    }

    function it_does_nothing_if_feature_flag_is_disabled(
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);

        $connectorProduct = $this->buildConnectorProduct('identifier_5', null);
        $productWithQualityScore = $this->buildConnectorProduct('identifier_5', new QualityScoreCollection([]));

        $this->fromConnectorProduct($connectorProduct)->shouldBeLike($productWithQualityScore);

        $this->fromConnectorProductList(
            new ConnectorProductList(1, [$connectorProduct])
        )->shouldBeLike(
            new ConnectorProductList(1, [$productWithQualityScore])
        );
    }

    function it_return_a_new_connector_product_with_quality_scores(
        GetProductScoresQueryInterface $getProductScoresQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $connectorProduct = $this->buildConnectorProduct('identifier_5', null);

        $qualityScores = new QualityScoreCollection(['ecommerce' => ['en_US' => new QualityScore('E', 15)]]);
        $getProductScoresQuery->byProductIdentifier('identifier_5')->willReturn($qualityScores);

        $productWithQualityScore = $this->buildConnectorProduct('identifier_5', $qualityScores);

        $this->fromConnectorProduct($connectorProduct)->shouldBeLike($productWithQualityScore);
    }

    function it_return_a_list_of_connector_product_with_quality_scores(
        GetProductScoresQueryInterface $getProductScoresQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $connectorProduct1 = $this->buildConnectorProduct('pdt_5', null);
        $connectorProduct2 = $this->buildConnectorProduct('pdt_6', null);

        $qualityScores1 = new QualityScoreCollection(['ecommerce' => ['en_US' => new QualityScore('E', 15)]]);
        $qualityScores2 = new QualityScoreCollection(['print' => ['en_US' => new QualityScore('A', 99)]]);
        $getProductScoresQuery->byProductIdentifiers(['pdt_5','pdt_6'])->willReturn([
            'pdt_5' => $qualityScores1,
            'pdt_6' => $qualityScores2,
        ]);

        $this->fromConnectorProductList(
            new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]),
            null,
            []
        )->shouldBeLike(
            new ConnectorProductList(2, [
                $this->buildConnectorProduct('pdt_5', $qualityScores1),
                $this->buildConnectorProduct('pdt_6', $qualityScores2),
            ])
        );
    }

    function it_return_a_list_of_connector_product_with_quality_scores_filtered_by_channel(
        GetProductScoresQueryInterface $getProductScoresQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $connectorProduct1 = $this->buildConnectorProduct('pdt_5', null);
        $connectorProduct2 = $this->buildConnectorProduct('pdt_6', null);

        $qualityScores1 = new QualityScoreCollection([
            'ecommerce' => ['en_US' => new QualityScore('E', 15), 'fr_FR' => new QualityScore('D', 62)],
            'print' => ['en_US' => new QualityScore('A', 99)],
        ]);
        $qualityScores2 = new QualityScoreCollection(['print' => ['en_US' => new QualityScore('A', 99)]]);

        $getProductScoresQuery->byProductIdentifiers(['pdt_5','pdt_6'])->willReturn([
            'pdt_5' => $qualityScores1,
            'pdt_6' => $qualityScores2,
        ]);

        $this->fromConnectorProductList(
            new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]),
            'ecommerce',
            []
        )->shouldBeLike(
            new ConnectorProductList(2, [
                $this->buildConnectorProduct('pdt_5', new QualityScoreCollection([
                    'ecommerce' => ['en_US' => new QualityScore('E', 15), 'fr_FR' => new QualityScore('D', 62)]
                ])),
                $this->buildConnectorProduct('pdt_6', new QualityScoreCollection([])),
            ])
        );
    }

    function it_return_a_list_of_connector_product_with_quality_scores_filtered_by_locales(
        GetProductScoresQueryInterface $getProductScoresQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $connectorProduct1 = $this->buildConnectorProduct('pdt_5', null);
        $connectorProduct2 = $this->buildConnectorProduct('pdt_6', null);

        $qualityScores1 = new QualityScoreCollection([
            'ecommerce' => [
                'en_US' => new QualityScore('E', 15),
                'fr_FR' => new QualityScore('D', 62),
                'de_DE' => new QualityScore('C', 76),
            ],
            'print' => ['en_US' => new QualityScore('D', 37)],
        ]);
        $qualityScores2 = new QualityScoreCollection(['print' => ['en_US' => new QualityScore('A', 99)]]);

        $getProductScoresQuery->byProductIdentifiers(['pdt_5','pdt_6'])->willReturn([
            'pdt_5' => $qualityScores1,
            'pdt_6' => $qualityScores2,
        ]);

        $this->fromConnectorProductList(
            new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]),
            null,
            ['en_US', 'fr_FR']
        )->shouldBeLike(
            new ConnectorProductList(2, [
                $this->buildConnectorProduct('pdt_5', new QualityScoreCollection([
                    'ecommerce' => [
                        'en_US' => new QualityScore('E', 15),
                        'fr_FR' => new QualityScore('D', 62),
                    ],
                    'print' => ['en_US' => new QualityScore('D', 37)],
                ])),
                $this->buildConnectorProduct('pdt_6', $qualityScores2),
            ])
        );
    }

    function it_return_a_list_of_connector_product_with_quality_scores_filtered_by_channel_and_locales(
        GetProductScoresQueryInterface $getProductScoresQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $connectorProduct1 = $this->buildConnectorProduct('pdt_5', null);
        $connectorProduct2 = $this->buildConnectorProduct('pdt_6', null);

        $qualityScores1 = new QualityScoreCollection([
            'ecommerce' => [
                'en_US' => new QualityScore('E', 15),
                'fr_FR' => new QualityScore('D', 62),
                'de_DE' => new QualityScore('C', 76),
            ],
            'print' => [
                'en_US' => new QualityScore('D', 37),
                'fr_FR' => new QualityScore('B', 81),
            ],
        ]);
        $qualityScores2 = new QualityScoreCollection(['print' => ['en_US' => new QualityScore('A', 99)]]);

        $getProductScoresQuery->byProductIdentifiers(['pdt_5','pdt_6'])->willReturn([
            'pdt_5' => $qualityScores1,
            'pdt_6' => $qualityScores2,
        ]);

        $this->fromConnectorProductList(
            new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]),
            'ecommerce',
            ['en_US']
        )->shouldBeLike(
            new ConnectorProductList(2, [
                $this->buildConnectorProduct('pdt_5', new QualityScoreCollection([
                    'ecommerce' => [
                        'en_US' => new QualityScore('E', 15),
                    ],
                ])),
                $this->buildConnectorProduct('pdt_6', new QualityScoreCollection([])),
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
            $qualityScore,
            null
        );
    }
}
