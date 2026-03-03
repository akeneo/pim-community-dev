<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

final class GetScoresPropertyStrategySpec extends ObjectBehavior
{
    public function let(FeatureFlag $allCriteriaFeature)
    {
        $this->beConstructedWith($allCriteriaFeature);
    }

    public function it_gets_the_scores_property_when_the_feature_all_criteria_is_enabled($allCriteriaFeature)
    {
        $allCriteriaFeature->isEnabled()->willReturn(true);

        $this->__invoke()->shouldReturn('scores');
    }

    public function it_gets_the_scores_property_when_the_feature_all_criteria_is_disabled($allCriteriaFeature)
    {
        $allCriteriaFeature->isEnabled()->willReturn(false);

        $this->__invoke()->shouldReturn('scores_partial_criteria');
    }
}
