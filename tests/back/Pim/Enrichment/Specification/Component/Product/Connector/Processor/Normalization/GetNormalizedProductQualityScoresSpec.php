<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Normalization;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetNormalizedProductQualityScoresSpec extends ObjectBehavior
{
    public function let(
        GetProductScoresQueryInterface $getProductScoresQuery,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $this->beConstructedWith($getProductScoresQuery, $dataQualityInsightsFeature);
    }

    public function it_returns_an_empty_array_when_the_feature_dqi_is_disabled(
        $dataQualityInsightsFeature,
        $getProductScoresQuery
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(false);

        $getProductScoresQuery->byProductUuid(Argument::any())->shouldNotBeCalled();

        $this->__invoke(Uuid::uuid4())->shouldReturn([]);
    }

    public function it_gets_normalized_quality_scores_without_filters_for_a_product(
        $getProductScoresQuery,
        $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $uuid = Uuid::uuid4();

        $getProductScoresQuery->byProductUuid($uuid)->willReturn(new QualityScoreCollection([
            'ecommerce' => [
                'en_US' => new QualityScore('A', 98),
                'fr_FR' => new QualityScore('B', 87),
            ]
        ]));

        $this->__invoke($uuid)->shouldBeLike([
            'ecommerce' => [
                'en_US' => 'A',
                'fr_FR' => 'B',
            ]
        ]);
    }

    public function it_gets_normalized_quality_scores_with_filters_on_channel_and_locales_for_a_product(
        $getProductScoresQuery,
        $dataQualityInsightsFeature
    ) {
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $uuid = Uuid::uuid4();

        $getProductScoresQuery->byProductUuid($uuid)->willReturn(new QualityScoreCollection([
            'ecommerce' => [
                'en_US' => new QualityScore('A', 98),
                'fr_FR' => new QualityScore('B', 87),
                'de_DE' => new QualityScore('B', 89),
            ],
            'mobile' => [
                'en_US' => new QualityScore('C', 78),
            ]
        ]));

        $this->__invoke($uuid, 'ecommerce', ['en_US', 'fr_FR'])->shouldBeLike([
            'ecommerce' => [
                'en_US' => 'A',
                'fr_FR' => 'B',
            ]
        ]);
    }
}
