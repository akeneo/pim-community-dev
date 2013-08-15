<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\Ajax;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Action\MassAction\Ajax\AjaxMassAction;
use Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction\MassActionWithExceptionsTestCase;

class AjaxMassActionTest extends MassActionWithExceptionsTestCase
{
    /**
     * @param array $options
     * @return MassActionInterface
     */
    protected function createMassAction(array $options)
    {
        return new AjaxMassAction($options);
    }

    /**
     * @return array
     */
    public function constructDataProvider()
    {
        return array(
            'minimum set of options' => array(
                'expectedOptions' => array(
                    'name'             => 'ajaxAction',
                    'handler'          => 'test.handler.service',
                    'frontend_type'    => 'ajax',
                    'route'            => 'oro_grid_mass_action',
                    'route_parameters' => array()
                ),
                'inputOptions' => array(
                    'name'    => 'ajaxAction',
                    'handler' => 'test.handler.service',
                ),
            ),
            'full set of options' => array(
                'expectedOptions' => array(
                    'name'             => 'ajaxAction',
                    'handler'          => 'test.handler.service',
                    'frontend_type'    => 'ajax',
                    'route'            => 'my_custom_route',
                    'route_parameters' => array('key' => 'value'),
                ),
                'inputOptions' => array(
                    'name'             => 'ajaxAction',
                    'handler'          => 'test.handler.service',
                    'route'            => 'my_custom_route',
                    'route_parameters' => array('key' => 'value'),
                ),
            )
        );
    }

    /**
     * @return array
     */
    public function constructExceptionDataProvider()
    {
        return array(
            'no name option' => array(
                'exceptionName' => '\InvalidArgumentException',
                'exceptionMessage' =>
                    'Option "name" is required for mass action class ' .
                    'Oro\Bundle\GridBundle\Action\MassAction\Ajax\AjaxMassAction',
                'inputOptions' => array(),
            ),
            'no route option' => array(
                'exceptionName' => '\InvalidArgumentException',
                'exceptionMessage' => 'Option "handler" is required for mass action "ajaxAction"',
                'inputOptions' => array(
                    'name' => 'ajaxAction'
                ),
            ),
        );
    }
}
