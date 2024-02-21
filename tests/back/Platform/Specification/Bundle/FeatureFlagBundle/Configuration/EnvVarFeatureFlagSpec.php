<?php

namespace Specification\Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration\EnvVarFeatureFlag;
use PhpSpec\ObjectBehavior;

class EnvVarFeatureFlagSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(true);
        $this->shouldHaveType(EnvVarFeatureFlag::class);
    }

    function it_is_a_feature_flag()
    {
        $this->beConstructedWith(true);
        $this->shouldImplement(FeatureFlag::class);
    }

    function it_is_by_default_disabled()
    {
        $this->beConstructedWith('');
        $this->isEnabled()->shouldReturn(false);
    }

    function it_tells_the_feature_is_enabled_from_a_boolean_env_var()
    {
        $this->beConstructedWith(true);
        $this->isEnabled()->shouldReturn(true);
    }

    function it_tells_the_feature_is_enabled_from_an_int_env_var()
    {
        $this->beConstructedWith(1);
        $this->isEnabled()->shouldReturn(true);
    }

    function it_tells_the_feature_is_disabled_from_a_boolean_env_var()
    {
        $this->beConstructedWith(false);
        $this->isEnabled()->shouldReturn(false);
    }

    function it_tells_the_feature_is_disabled_from_an_int_env_var()
    {
        $this->beConstructedWith(0);
        $this->isEnabled()->shouldReturn(false);
    }
}
