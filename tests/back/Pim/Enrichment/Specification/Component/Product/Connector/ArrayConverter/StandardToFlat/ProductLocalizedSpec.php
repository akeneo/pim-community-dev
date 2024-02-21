<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

class ProductLocalizedSpec extends ObjectBehavior
{
    function let(ArrayConverterInterface $converter, AttributeConverterInterface $delocalizer)
    {
        $this->beConstructedWith($converter, $delocalizer);
    }

    function it_converts($converter, $delocalizer)
    {
        $delocalizer->convertToLocalizedFormats(['the item standardized'], ['the options'])
            ->willReturn(['the item standardized and localized']);
        $converter->convert(['values' => ['the item standardized and localized']], ['the options'])
            ->willReturn(['the flat item localized']);

        $this->convert(['values' => ['the item standardized']], ['the options'])
            ->shouldReturn(['the flat item localized']);
    }
}
