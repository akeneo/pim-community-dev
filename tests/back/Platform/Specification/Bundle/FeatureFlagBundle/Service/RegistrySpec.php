<?php

namespace Specification\Akeneo\Platform\Bundle\FeatureFlagBundle\Service;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Service\Registry;
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
        $flag = new CustomFlag();
        $this->add('foo', $flag);

        $this->get('foo')->shouldReturn($flag);
    }

    function it_fails_when_getting_an_unknow_flag()
    {
        $flag = new CustomFlag();
        $this->add('foo', $flag);

        $this->shouldThrow(InvalidArgumentException::class)->during('get', ['bar']);
    }
}

class CustomFlag implements FeatureFlag
{
    public function isEnabled(): bool
    {
        return true;
    }
}
