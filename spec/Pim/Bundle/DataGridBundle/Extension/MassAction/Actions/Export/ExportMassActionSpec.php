<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Export;

use Prophecy\Argument;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

use PhpSpec\ObjectBehavior;

class ExportMassActionSpec extends ObjectBehavior
{
    function it_must_implements_export_mass_action_interface()
    {
        $this->shouldBeAnInstanceOf(
            'Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Export\ExportMassActionInterface'
        );
    }

    function it_should_have_required_format_route_parameter()
    {
        $params = array();
        $options = ActionConfiguration::createNamed('export', $params);

        $this->shouldThrow(
            new \LogicException('There is no route_parameter named "_format" for action "export"')
        )->duringSetOptions($options);
    }

    function it_should_have_required_content_type_route_parameter()
    {
        $params = array(
            'route_parameters' => array('_format' => 'foo')
        );
        $options = ActionConfiguration::createNamed('export', $params);

        $this->shouldThrow(
            new \LogicException('There is no route_parameter named "_contentType" for action "export"')
        )->duringSetOptions($options);
    }

    function it_should_defined_default_values()
    {
        $routeParams = array('_format' => 'foo', '_contentType' => 'bar');
        $params = array('route_parameters' => $routeParams);
        $options = ActionConfiguration::createNamed('export', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('export');
        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('export');
        $this->getOptions()->offsetGet('context')->shouldReturn(array());
        $this->getOptions()->offsetGet('route')->shouldReturn('pim_datagrid_export_index');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn($routeParams);
        $this
            ->getOptions()
            ->offsetGet('handler')
            ->shouldReturn('pim_datagrid.extension.mass_action.handler.export');
    }

    function it_should_overwrite_default_values()
    {
        $routeParams = array('_format' => 'foo', '_contentType' => 'bar');
        $context     = array('baz' => 'qux');
        $params = array(
            'route_parameters' => $routeParams,
            'frontend_type'    => 'my_frontend',
            'context'          => $context,
            'route'            => 'my_route',
            'handler'          => 'my_handler'
        );
        $options = ActionConfiguration::createNamed('export', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getOptions()->getName()->shouldReturn('export');
        $this->getOptions()->offsetGet('frontend_type')->shouldReturn('my_frontend');
        $this->getOptions()->offsetGet('context')->shouldReturn($context);
        $this->getOptions()->offsetGet('route')->shouldReturn('my_route');
        $this->getOptions()->offsetGet('route_parameters')->shouldReturn($routeParams);
        $this->getOptions()->offsetGet('handler')->shouldReturn('my_handler');
    }

    function it_should_get_export_context()
    {
        $routeParams = array('_format' => 'foo', '_contentType' => 'bar');
        $context     = array('baz' => 'qux');
        $params = array(
                'route_parameters' => $routeParams,
                'context'          => $context
        );
        $options = ActionConfiguration::createNamed('export', $params);

        $this->setOptions($options)->shouldNotThrow(Argument::any());

        $this->getExportContext()->shouldReturn($context);
    }
}
