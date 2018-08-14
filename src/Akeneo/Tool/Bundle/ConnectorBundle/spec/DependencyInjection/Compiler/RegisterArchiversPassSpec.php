<?php

namespace spec\Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterArchiversPassSpec extends ObjectBehavior
{
    function it_is_a_compiler_pass()
    {
        $this->shouldImplement('\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_does_not_process_anything_else_than_an_archivist_event_listener(ContainerBuilder $container)
    {
        $container->hasDefinition('pim_connector.event_listener.archivist')->willReturn(false);

        $this->process($container)->shouldReturn(null);
    }

    function it_processes_an_archivist_event_listener_container(ContainerBuilder $container, Definition $service)
    {
        $container->hasDefinition('pim_connector.event_listener.archivist')->willReturn(true);
        $container->getDefinition('pim_connector.event_listener.archivist')->willReturn($service);
        $container->findTaggedServiceIds('pim_connector.archiver')->willReturn([
            'pim_connector.archiver.invalid_item_csv_archiver' => [[]],
            'pim_connector.archiver.file_reader_archiver' => [[]]
        ]);

        $service
            ->addMethodCall('registerArchiver', Argument::type('array'))
            ->shouldBeCalledTimes(2);

        $this->process($container);
    }
}
