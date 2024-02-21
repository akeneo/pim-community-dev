<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class FieldChainedProviderSpec extends ObjectBehavior
{
    function it_should_register_providers(FieldProviderInterface $provider, AttributeInterface $attribute)
    {
        $this->addProvider($provider);

        $provider->supports($attribute)->willReturn(true);
        $provider->getField($attribute)->willReturn('akeneo_attribute_field');

        $this->getField($attribute)->shouldReturn('akeneo_attribute_field');
    }

    function it_should_throw_an_exception_if_no_supporting_provider_is_registred(FieldProviderInterface $provider, AttributeInterface $attribute)
    {
        $this->addProvider($provider);

        $provider->supports($attribute)->willReturn(false);

        $this->shouldThrow('\RuntimeException')->during('getField', [$attribute]);
    }
}
