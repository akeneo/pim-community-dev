<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\Widget;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Action\MassAction\Widget\WindowMassAction;
use Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\MassActionTestCase;

class WindowMassActionTest extends MassActionTestCase
{
    /**
     * @param array $options
     * @return MassActionInterface
     */
    protected function createMassAction(array $options)
    {
        return new WindowMassAction($options);
    }

    /**
     * @return array
     */
    public function constructDataProvider()
    {
        return array(
            'minimum set of options' => array(
                'expectedOptions' => array(
                    'name'             => 'windowAction',
                    'frontend_options' => array(),
                    'frontend_type'    => 'dialog',
                    'route'            => 'some_route',
                    'route_parameters' => array(),
                ),
                'inputOptions'    => array(
                    'name'          => 'windowAction',
                    'route'         => 'some_route',

                ),
            ),
            'full set of options'    => array(
                'expectedOptions' => array(
                    'name'             => 'windowAction',
                    'frontend_options' => array('key' => 'value'),
                    'route'            => 'some_route',
                    'frontend_type'    => 'dialog',
                    'route_parameters' => array('key' => 'value'),
                ),
                'inputOptions'    => array(
                    'name'             => 'windowAction',
                    'route'            => 'some_route',
                    'frontend_options' => array('key' => 'value'),
                    'route_parameters' => array('key' => 'value'),
                ),
            )
        );
    }
}
