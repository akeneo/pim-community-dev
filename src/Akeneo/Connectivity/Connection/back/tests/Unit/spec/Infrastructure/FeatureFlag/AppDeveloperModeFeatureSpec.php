<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\FeatureFlag;

use Akeneo\Connectivity\Connection\Infrastructure\FeatureFlag\AppDeveloperModeFeature;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;

class AppDeveloperModeFeatureSpec extends ObjectBehavior
{
    public function it_is_a_feature_flag(): void
    {
        $this->beConstructedWith('community_dev');
        $this->shouldHaveType(AppDeveloperModeFeature::class);
        $this->shouldImplement(FeatureFlag::class);
    }

    public function it_is_not_active_by_default(): void
    {
        $this->beConstructedWith('community_dev');
        $this->isEnabled(null)->shouldReturn(false);
    }

    public function it_is_enabled_for_the_right_product_code(): void
    {
        $this->beConstructedWith('serenity_dev');
        $this->isEnabled(null)->shouldReturn(true);
    }
}
