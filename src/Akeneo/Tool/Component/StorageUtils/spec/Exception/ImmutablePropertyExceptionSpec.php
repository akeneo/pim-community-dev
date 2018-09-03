<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use PhpSpec\ObjectBehavior;

class ImmutablePropertyExceptionSpec extends ObjectBehavior
{
    function it_creates_an_immutable_property_exception()
    {
        $exception = ImmutablePropertyException::immutableProperty(
            'property',
            'property_value',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute'
        );

        $this->beConstructedWith(
            'property',
            'property_value',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "property" cannot be modified, "property_value" given.',
            0
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn('property');
        $this->getPropertyValue()->shouldReturn($exception->getPropertyValue());
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }
}
