<?php

namespace Specification\Akeneo\Platform\Bundle\FeatureFlagBundle\Internal;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;

class RegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Registry::class);
    }

    function it_registers_feature_flags()
    {
        $flag = new CustomFlagEnabled();
        $this->add('foo', $flag);

        $this->get('foo')->shouldReturn($flag);
    }

    function it_fails_when_getting_an_unknow_flag()
    {
        $flag = new CustomFlagEnabled();
        $this->add('foo', $flag);

        $this->shouldThrow(InvalidArgumentException::class)->during('get', ['bar']);
    }

    function it_returns_all_feature_flags()
    {
        $foo = new CustomFlagEnabled();
        $this->add('foo', $foo);

        $bar = new CustomFlagDisabled();
        $this->add('bar', $bar);

        $this->all()->shouldReturn([
            'foo' => true,
            'bar' => false
        ]);
    }
}

class CustomFlagEnabled implements FeatureFlag
{
    public function isEnabled(?string $feature = null): bool
    {
        return true;
    }
}
class CustomFlagDisabled implements FeatureFlag
{
    public function isEnabled(?string $feature = null): bool
    {
        return false;
    }
}
