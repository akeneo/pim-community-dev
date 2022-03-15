<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetNormalizedProductModelQualityScoresSpec extends ObjectBehavior
{
    public function let(
        GetProductModelScoresQueryInterface $getProductModelScoresQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $this->beConstructedWith($getProductModelScoresQuery, $dataQualityInsightsFeature);
    }

    public function it_returns_an_empty_array_when_the_feature_dqi_is_disabled(
        $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);

        $this->__invoke('a_product_model')->shouldReturn([]);
    }

    public function it_gets_normalized_quality_scores_without_filters_for_a_product_model(
        $getProductModelScoresQuery,
        $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $getProductModelScoresQuery->byProductModelCode('a_product_model')->willReturn(new QualityScoreCollection([
            'ecommerce' => [
                'en_US' => new QualityScore('A', 98),
                'fr_FR' => new QualityScore('B', 87),
            ]
        ]));

        $this->__invoke('a_product_model')->shouldBeLike([
            'ecommerce' => [
                'en_US' => 'A',
                'fr_FR' => 'B',
            ]
        ]);
    }

    public function it_gets_normalized_quality_scores_with_filters_on_channel_and_locales_for_a_product_model(
        $getProductModelScoresQuery,
        $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $getProductModelScoresQuery->byProductModelCode('a_product_model')->willReturn(new QualityScoreCollection([
            'ecommerce' => [
                'en_US' => new QualityScore('A', 98),
                'fr_FR' => new QualityScore('B', 87),
                'de_DE' => new QualityScore('B', 89),
            ],
            'mobile' => [
                'en_US' => new QualityScore('C', 78),
            ]
        ]));

        $this->__invoke('a_product_model', 'ecommerce', ['en_US', 'fr_FR'])->shouldBeLike([
            'ecommerce' => [
                'en_US' => 'A',
                'fr_FR' => 'B',
            ]
        ]);
    }
}
