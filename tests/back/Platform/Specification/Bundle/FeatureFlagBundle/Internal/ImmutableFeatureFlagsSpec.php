<?php

namespace Specification\Akeneo\Platform\Bundle\FeatureFlagBundle\Internal;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;

class ImmutableFeatureFlagsSpec extends ObjectBehavior
{
    function let()
    {
        $registry = new Registry();
        $registry->add('foo', new Enabled());
        $registry->add('bar', new Disabled());

        $this->beConstructedWith($registry);
    }

    function it_tells_if_a_feature_is_enabled_or_not()
    {
        $this->isEnabled('foo')->shouldReturn(true);
        $this->isEnabled('bar')->shouldReturn(false);
    }

    function it_throws_an_exception_if_the_feature_does_not_exist()
    {
        $this->shouldThrow(InvalidArgumentException::class)->during('isEnabled', ['baz']);
    }
}

class Enabled implements FeatureFlag
{
    public function isEnabled(?string $feature = null): bool
    {
        return true;
    }
}

class Disabled implements FeatureFlag
{
    public function isEnabled(?string $feature = null): bool
    {
        return false;
    }
}
