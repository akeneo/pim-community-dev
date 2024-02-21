<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\MandatoryPropertyException;
use PhpSpec\ObjectBehavior;

class MandatoryPropertyExceptionSpec extends ObjectBehavior
{
    function it_creates_a_mandatory_property_exception()
    {
        $exception = MandatoryPropertyException::mandatoryProperty(
            'property',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\FamilyVariant'
        );

        $this->beConstructedWith(
            'property',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\FamilyVariant',
            'Property "property" is mandatory.',
            0
        );

        $this->shouldBeAnInstanceOf(get_class($exception));
        $this->getPropertyName()->shouldReturn('property');
        $this->getClassName()->shouldReturn($exception->getClassName());
        $this->getMessage()->shouldReturn($exception->getMessage());
        $this->getCode()->shouldReturn($exception->getCode());
    }
}
