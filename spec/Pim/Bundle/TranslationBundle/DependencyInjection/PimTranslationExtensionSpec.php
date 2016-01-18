<?php

namespace spec\Pim\Bundle\TranslationBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimTranslationExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TranslationBundle\DependencyInjection\PimTranslationExtension');
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

        $this->load([], $container);
    }
}
