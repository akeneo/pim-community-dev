<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;

class ValueConverterRegistrySpec extends ObjectBehavior
{
    function it_gets_converters(AbstractValueConverter $converter)
    {
        $converter->supportsField('pim_catalog_identifier')->willReturn(true);
        $this->register($converter, 100);

        $this->getConverter('pim_catalog_identifier')->shouldReturn($converter);
    }

    function it_does_not_find_supported_converters(AbstractValueConverter $converter)
    {
        $converter->supportsField('pim_catalog_identifier')->willReturn(false);
        $this->register($converter, 100);

        $this->getConverter('pim_catalog_identifier')->shouldReturn(null);
    }
}
