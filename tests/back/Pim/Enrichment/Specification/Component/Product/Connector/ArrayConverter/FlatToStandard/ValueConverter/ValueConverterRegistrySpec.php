<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;

class ValueConverterRegistrySpec extends ObjectBehavior
{
    function it_is_a_registry()
    {
        $this->shouldImplement(
            '\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterRegistryInterface'
        );
    }

    function it_gets_converters(ValueConverterInterface $converter)
    {
        $converter->supportsField('pim_catalog_identifier')->willReturn(true);
        $this->register($converter, 100);

        $this->getConverter('pim_catalog_identifier')->shouldReturn($converter);
    }

    function it_does_not_find_supported_converters(ValueConverterInterface $converter)
    {
        $converter->supportsField('pim_catalog_identifier')->willReturn(false);
        $this->register($converter, 100);

        $this->getConverter('pim_catalog_identifier')->shouldReturn(null);
    }
}
