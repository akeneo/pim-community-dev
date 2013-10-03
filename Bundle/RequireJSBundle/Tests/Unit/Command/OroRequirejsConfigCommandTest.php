<?php

namespace Oro\Bundle\RequireJSBundle\Tests\Unit\Command;

use Oro\Bundle\RequireJSBundle\Command\OroRequirejsConfigCommand;
use Zend\Server\Reflection\ReflectionMethod;

class OroRequirejsConfigCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $command = new OroRequirejsConfigCommand('test');
        $this->assertEquals('oro:requirejs:config', $command->getName());
    }

    public function testCombineConfig()
    {
        $parameters = array(
            'oro_require_js' => array(
                'config' => array(),
            ),
            'kernel.bundles' => array('Oro\Bundle\RequireJSBundle\Tests\Unit\Fixtures\TestBundle')
        );

        $expected = array(
            'paths' => array(
                'oro/test' => 'orotest/js/test',
            )
        );

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(
                function ($name) use (&$parameters) {
                    return $parameters[$name];
                }
            ));

        $command = $this->getMockBuilder('Oro\Bundle\RequireJSBundle\Command\OroRequirejsConfigCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('getContainer'))
            ->getMock();
        $command->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $method = new \ReflectionMethod(
            'Oro\Bundle\RequireJSBundle\Command\OroRequirejsConfigCommand', 'combineConfig');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($command));
    }
}
