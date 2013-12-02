<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\Widget;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Action\MassAction\Widget\WidgetMassAction;
use Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\MassActionTestCase;

class WidgetMassActionTest extends MassActionTestCase
{
    /**
     * @param array $options
     * @return MassActionInterface
     */
    protected function createMassAction(array $options)
    {
        return new WidgetMassAction($options);
    }

    /**
     * @return array
     */
    public function constructDataProvider()
    {
        return array(
            'minimum set of options' => array(
                'expectedOptions' => array(
                    'name'             => 'widgetAction',
                    'frontend_options' => array(),
                    'frontend_type'    => 'widget',
                    'route'            => 'some_route',
                    'route_parameters' => array(),
                ),
                'inputOptions'    => array(
                    'name'          => 'widgetAction',
                    'route'         => 'some_route',
                    'frontend_type' => 'widget',

                ),
            ),
            'full set of options'    => array(
                'expectedOptions' => array(
                    'name'             => 'widgetAction',
                    'frontend_options' => array('key' => 'value'),
                    'route'            => 'some_route',
                    'frontend_type'    => 'widget',
                    'route_parameters' => array('key' => 'value'),
                ),
                'inputOptions'    => array(
                    'name'             => 'widgetAction',
                    'route'            => 'some_route',
                    'frontend_type'    => 'widget',
                    'frontend_options' => array('key' => 'value'),
                    'route_parameters' => array('key' => 'value'),
                ),
            )
        );
    }
}
