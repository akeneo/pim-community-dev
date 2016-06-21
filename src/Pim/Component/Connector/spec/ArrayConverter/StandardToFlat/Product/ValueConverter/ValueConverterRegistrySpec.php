<?php

namespace spec\Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\AbstractValueConverter;

class ValueConverterRegistrySpec extends ObjectBehavior
{
    function it_increments_priority_if_service_already_registered(
        AbstractValueConverter $converter1,
        AbstractValueConverter $converter2
    ) {
        $converter1->supportsAttribute('pim_catalog_identifier')->willReturn(true);
        $converter2->supportsAttribute('pim_catalog_identifier')->willReturn(true);

        $this->register($converter1, 100);
        $this->register($converter2, 100);

        $this->getConverter('pim_catalog_identifier')->shouldReturn($converter1);
    }

    function it_gets_converters(AbstractValueConverter $converter)
    {
        $converter->supportsAttribute('pim_catalog_identifier')->willReturn(true);
        $this->register($converter, 100);

        $this->getConverter('pim_catalog_identifier')->shouldReturn($converter);
    }

    function it_does_not_find_supported_converters(AbstractValueConverter $converter)
    {
        $converter->supportsAttribute('pim_catalog_identifier')->willReturn(false);
        $this->register($converter, 100);

        $this->getConverter('pim_catalog_identifier')->shouldReturn(null);
    }
}
