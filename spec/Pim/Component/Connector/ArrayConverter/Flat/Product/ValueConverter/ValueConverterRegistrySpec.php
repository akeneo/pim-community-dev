<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\ValueConverterInterface;

class ValueConverterRegistrySpec extends ObjectBehavior
{
    function it_is_a_registry()
    {
        $this->shouldImplement(
            'Pim\Component\Connector\ArrayConverter\Flat\Product\ValueConverter\ValueConverterRegistryInterface'
        );
    }

    function it_gets_converters(ValueConverterInterface $converter)
    {
        $converter->supportsField('pim_catalog_identifier')->willReturn(true);
        $this->register($converter);

        $this->getConverter('pim_catalog_identifier')->shouldReturn($converter);
    }

    function it_does_not_find_supported_converters(ValueConverterInterface $converter)
    {
        $converter->supportsField('pim_catalog_identifier')->willReturn(false);
        $this->register($converter);

        $this->getConverter('pim_catalog_identifier')->shouldReturn(null);
    }
}
