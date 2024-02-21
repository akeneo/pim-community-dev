<?php

namespace Specification\Akeneo\Pim\Structure\Component;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypeInterface;

class AttributeTypeRegistrySpec extends ObjectBehavior
{
    function let(FeatureFlags $flags)
    {
        $this->beConstructedWith($flags);
        $flags->isEnabled('asset')->willReturn(true);
        $flags->isEnabled('reference_entity')->willReturn(false);
    }

    function it_get_by_default_an_alias_of_an_attribute_type_if_no_feature_is_associated(AttributeTypeInterface $type)
    {
        $this->getAliases()->shouldHaveCount(0);
        $this->register('my_type', $type)->shouldReturn($this);
        $this->getAliases()->shouldHaveCount(1);
        $this->get('my_type')->shouldReturn($type);
    }

    function it_get_an_alias_of_an_attribute_type_if_feature_is_enable(AttributeTypeInterface $type)
    {
        $this->getAliases()->shouldHaveCount(0);
        $this->register('my_type', $type, 'asset')->shouldReturn($this);
        $this->getAliases()->shouldHaveCount(1);
        $this->get('my_type')->shouldReturn($type);
    }

    function it_throws_an_exception_when_getting_alias_of_an_attribute_type_if_feature_is_disabled(AttributeTypeInterface $type)
    {
        $this->register('my_type', $type, 'reference_entity')->shouldReturn($this);
        $this->getAliases()->shouldHaveCount(0);
        $this->shouldThrow(\LogicException::class)->during('get', ['my_type']);
    }

    function it_throws_exception_when_try_to_fetch_a_not_registered_attribute_type()
    {
        $this->shouldThrow(new \LogicException('Attribute type "unknown" is not registered'))->duringGet('unknown');
    }
}
