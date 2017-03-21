<?php

namespace spec\Pim\Bundle\DataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterExtensionSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        FiltersConfigurator $configurator,
        Manager $manager
    ) {
        $container->get('oro_datagrid.datagrid.manager')->willReturn($manager);
        $container->get('pim_datagrid.datagrid.configuration.product.filters_configurator')->willReturn($configurator);
        $this->beConstructedWith($container);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('Twig_Extension');
    }

    function it_have_a_name()
    {
        $this->getName()->shouldReturn('pim_datagrid_filter_extension');
    }

    function it_have_a_filter_label_function()
    {
        $this->getFunctions()->shouldHaveKey('filter_label');
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

        $this
            ->shouldThrow(new \LogicException('Attribute "foo" does not exists'))
            ->duringFilterLabel('foo');
    }

    function it_gives_the_label_of_a_filter(
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
        $configuration->offsetGetByPath('[filters][columns][foo][label]')->willReturn('Foo');

        $this->filterLabel('foo')->shouldReturn('Foo');
    }
}
