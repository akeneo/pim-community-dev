<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export\ExportMassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExportMassActionSpec extends ObjectBehavior
{
    function it_is_an_export_mass_action()
    {
        $this->shouldImplement(
            ExportMassActionInterface::class
        );
    }

    function it_requires_the_format_route_parameter()
    {
        $options = ActionConfiguration::createNamed('export', []);

        $this->shouldThrow(
            new \LogicException('There is no route_parameter named "_format" for action "export"')
        )->duringSetOptions($options);
    }

    function it_requires_the_content_type_route_parameter()
    {
        $params = [
            'route_parameters' => ['_format' => 'foo']
        ];
        $options = ActionConfiguration::createNamed('export', $params);

        $this->shouldThrow(
            new \LogicException('There is no route_parameter named "_contentType" for action "export"')
        )->duringSetOptions($options);
    }

    function it_defines_default_values()
    {
        $routeParams = ['_format' => 'foo', '_contentType' => 'bar'];
        $params = ['route_parameters' => $routeParams];
        $options = ActionConfiguration::createNamed('export', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('export');
        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('export');
        $this->getOptions()->offsetGet('context')->shouldReturn(array());
        $this->getOptions()->offsetGet('route')->shouldReturn('pim_datagrid_export_index');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn($routeParams);
        $this->getOptions()->offsetGet('handler')->shouldReturn('quick_export');
    }

    function it_overwrites_default_values()
    {
        $routeParams = ['_format' => 'foo', '_contentType' => 'bar'];
        $context = ['baz' => 'qux'];
        $params = [
            'route_parameters' => $routeParams,
            'context'          => $context,
            'route'            => 'my_route',
            'handler'          => 'my_handler'
        ];
        $options = ActionConfiguration::createNamed('export', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('export');
        $this->getOptions()->offsetGet('context')->shouldReturn($context);
        $this->getOptions()->offsetGet('route')->shouldReturn('my_route');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn($routeParams);
        $this->getOptions()->offsetGet('handler')->shouldReturn('my_handler');
    }

    function it_gets_export_context()
    {
        $routeParams = ['_format' => 'foo', '_contentType' => 'bar'];
        $context = ['baz' => 'qux'];
        $params = [
            'route_parameters' => $routeParams,
            'context'          => $context
        ];
        $options = ActionConfiguration::createNamed('export', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getExportContext()->shouldReturn($context);
    }

    function it_doesnt_allow_overriding_frontend_type()
    {
        $routeParams = ['_format' => 'foo', '_contentType' => 'bar'];
        $params = ['route_parameters' => $routeParams, 'frontend_type' => 'bar'];
        $options = ActionConfiguration::createNamed('edit', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('export');
    }
}
