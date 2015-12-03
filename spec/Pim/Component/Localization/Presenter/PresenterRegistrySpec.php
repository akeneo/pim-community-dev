<?php

namespace spec\Pim\Component\Localization\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Localization\Presenter\PresenterInterface;
use Prophecy\Argument;

class PresenterRegistrySpec extends ObjectBehavior
{
    function it_is_a_localizer_registry()
    {
        $this->shouldImplement('Pim\Component\Localization\Presenter\PresenterRegistryInterface');
    }

    function it_get_localizer(PresenterInterface $presenter)
    {
        $presenter->supports('pim_catalog_number')->willReturn(true);
        $this->register($presenter, 'attribute');
        $this->getAttributePresenter('pim_catalog_number')->shouldReturn($presenter);
    }

    function it_returns_null_if_there_is_no_localizer(PresenterInterface $presenter)
    {
        $presenter->supports('pim_catalog_number')->willReturn(false);
        $this->register($presenter, 'attribute');
        $this->getAttributePresenter('pim_catalog_number')->shouldReturn(null);
    }

    function it_get_product_value_localizer(PresenterInterface $presenter)
    {
        $presenter->supports('pim_catalog_number')->willReturn(true);
        $this->register($presenter, 'attribute_option');
        $this->getAttributeOptionPresenter('pim_catalog_number')->shouldReturn($presenter);
    }

    function it_returns_null_if_there_is_no_product_value_localizer(PresenterInterface $presenter)
    {
        $presenter->supports('pim_catalog_number')->willReturn(false);
        $this->register($presenter, 'attribute_option');
        $this->getAttributeOptionPresenter('pim_catalog_number')->shouldReturn(null);
    }
}
