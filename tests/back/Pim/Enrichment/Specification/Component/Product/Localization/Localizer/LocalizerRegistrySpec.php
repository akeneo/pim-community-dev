<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\LocalizerRegistryInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;

class LocalizerRegistrySpec extends ObjectBehavior
{
    function it_is_a_localizer_registry()
    {
        $this->shouldImplement(LocalizerRegistryInterface::class);
    }

    function it_get_localizer(LocalizerInterface $localizer)
    {
        $localizer->supports('pim_catalog_number')->willReturn(true);
        $this->register($localizer);
        $this->getLocalizer('pim_catalog_number')->shouldReturn($localizer);
    }

    function it_returns_null_if_there_is_no_localizer(LocalizerInterface $localizer)
    {
        $localizer->supports('pim_catalog_number')->willReturn(false);
        $this->register($localizer);
        $this->getLocalizer('pim_catalog_number')->shouldReturn(null);
    }
}
