<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class EmptyValueChainedProviderSpec extends ObjectBehavior
{
    function it_should_register_providers(EmptyValueProviderInterface $provider, AttributeInterface $attribute)
    {
        $this->addProvider($provider);

        $provider->supports($attribute)->willReturn(true);
        $provider->getEmptyValue($attribute)->willReturn('akeneo_attribute_field');

        $this->getEmptyValue($attribute)->shouldReturn('akeneo_attribute_field');
    }

    function it_should_throw_an_exception_if_no_supporting_provider_is_registred(
        EmptyValueProviderInterface $provider,
        AttributeInterface $attribute
    ) {
        $this->addProvider($provider);

        $provider->supports($attribute)->willReturn(false);

        $this->shouldThrow('\RuntimeException')->during('getEmptyValue', [$attribute]);
    }
}
