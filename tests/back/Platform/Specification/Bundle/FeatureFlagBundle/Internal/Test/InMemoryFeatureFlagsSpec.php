<?php

namespace Specification\Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;

class InMemoryFeatureFlagsSpec extends ObjectBehavior
{
    function let()
    {
        $registry = new Registry();
        $registry->add('foo', new Enabled());
        $registry->add('bar', new Disabled());

        $this->beConstructedWith($registry);
    }

    function it_is_disabled_by_default_when_asking_for_an_existing_feature_flag()
    {
        $this->isEnabled('foo')->shouldReturn(false);
        $this->isEnabled('bar')->shouldReturn(false);
    }

    function it_is_enabled_only_if_explicitly_enable()
    {
        $this->enable('bar');

        $this->isEnabled('foo')->shouldReturn(false);
        $this->isEnabled('bar')->shouldReturn(true);
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
