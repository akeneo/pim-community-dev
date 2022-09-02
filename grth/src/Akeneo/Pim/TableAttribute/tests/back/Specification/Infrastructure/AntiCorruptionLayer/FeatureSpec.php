<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\Feature;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use PhpSpec\ObjectBehavior;

class FeatureSpec extends ObjectBehavior
{
    function let(FeatureFlags $featureFlags)
    {
        $featureFlags->isEnabled('enabled_feature')->willReturn(true);
        $featureFlags->isEnabled('disabled_feature')->willReturn(false);
        $featureFlags->isEnabled('unknown_feature')->willThrow(new \InvalidArgumentException());
        $this->beConstructedWith($featureFlags);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Feature::class);
    }

    function it_returns_true_when_feature_is_enabled()
    {
        $this->isEnabled('enabled_feature')->shouldReturn(true);
    }

    function it_returns_false_when_feature_is_disabled()
    {
        $this->isEnabled('disabled_feature')->shouldReturn(false);
    }

    function it_returns_false_when_feature_is_unknown()
    {
        $this->isEnabled('unknown_feature')->shouldReturn(false);
    }
}
