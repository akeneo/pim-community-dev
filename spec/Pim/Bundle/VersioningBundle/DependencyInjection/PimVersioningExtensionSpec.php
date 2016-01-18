<?php

namespace spec\Pim\Bundle\VersioningBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimVersioningExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\DependencyInjection\PimVersioningExtension');
    }

    function it_is_a_bundle_extension()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\DependencyInjection\Extension');
    }

    function it_loads_configuration_files(ContainerBuilder $container)
    {
        $container->addResource(Argument::any())->shouldBeCalled();
        $container->setParameter(Argument::cetera())->shouldBeCalled();
        $container->setDefinition(Argument::cetera())->shouldBeCalled();
        $container->getParameter('pim_catalog_product_storage_driver')->willReturn('foo');

        $this->load([], $container);
    }
}
