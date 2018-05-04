<?php

namespace spec\Pim\Component\Catalog\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;

class LocalizerRegistrySpec extends ObjectBehavior
{
    function it_is_a_localizer_registry()
    {
        $this->shouldImplement('Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface');
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
