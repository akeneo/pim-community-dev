<?php

namespace spec\Akeneo\Component\Localization\Localizer;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocalizerRegistrySpec extends ObjectBehavior
{
    function it_is_a_localizer_registry()
    {
        $this->shouldImplement('Akeneo\Component\Localization\Localizer\LocalizerRegistryInterface');
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
