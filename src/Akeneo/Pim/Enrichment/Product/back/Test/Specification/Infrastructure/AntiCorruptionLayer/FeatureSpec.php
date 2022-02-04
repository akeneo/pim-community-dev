<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\Feature;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use PhpSpec\ObjectBehavior;

class FeatureSpec extends ObjectBehavior
{
    function let(FeatureFlags $featureFlags)
    {
        $this->beConstructedWith($featureFlags);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Feature::class);
    }

    function it_returns_true_when_feature_is_enabled(FeatureFlags $featureFlags)
    {
        $featureFlags->isEnabled('feature')->willReturn(true);

        $this->isEnabled('feature')->shouldReturn(true);
    }

    function it_returns_false_when_feature_is_not_enabled(FeatureFlags $featureFlags)
    {
        $featureFlags->isEnabled('feature')->willReturn(false);

        $this->isEnabled('feature')->shouldReturn(false);
    }

    function it_returns_false_when_feature_is_unknown(FeatureFlags $featureFlags)
    {
        $featureFlags->isEnabled('feature')->willThrow(new \InvalidArgumentException('unknown'));

        $this->isEnabled('feature')->shouldReturn(false);
    }
}
