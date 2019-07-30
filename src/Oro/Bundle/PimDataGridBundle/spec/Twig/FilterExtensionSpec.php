<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FilterExtensionSpec extends ObjectBehavior
{
    function let(
        FiltersConfigurator $configurator,
        TranslatorInterface $translator,
        Manager $manager
    ) {;
        $this->beConstructedWith($manager, $configurator, $translator);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('Twig_Extension');
    }

    function it_throws_an_exception_when_i_try_to_get_the_label_of_an_unknown_filter(
        $manager,
        $configurator,
        DatagridInterface $datagrid,
        Acceptor $acceptor,
        DatagridConfiguration $configuration
    ) {
        $acceptor->getConfig()->willReturn($configuration);
        $datagrid->getAcceptor()->willReturn($acceptor);
        $manager->getDatagrid('product-grid')->willReturn($datagrid);
        $configurator->configure($configuration)->shouldBeCalled();
        $configuration->offsetGetByPath('[filters][columns][foo][label]')->willReturn(null);

        $this->filterLabel('foo')->shouldReturn(null);
    }

    function it_gives_the_label_of_a_filter(
        $manager,
        $configurator,
        $translator,
        DatagridInterface $datagrid,
        Acceptor $acceptor,
        DatagridConfiguration $configuration
    ) {
        $acceptor->getConfig()->willReturn($configuration);
        $datagrid->getAcceptor()->willReturn($acceptor);
        $manager->getDatagrid('product-grid')->willReturn($datagrid);
        $configurator->configure($configuration)->shouldBeCalled();
        $configuration->offsetGetByPath('[filters][columns][foo][label]')->willReturn('Foo');
        $translator->trans('Foo')->willReturn('Foo');

        $this->filterLabel('foo')->shouldReturn('Foo');
    }
}
