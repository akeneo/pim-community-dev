<?php

namespace Oro\Bundle\RequireJSBundle\Tests\Unit\Command;

use Oro\Bundle\RequireJSBundle\Command\OroRequirejsBuildCommand;
use Zend\Server\Reflection\ReflectionMethod;

class OroRequirejsBuildCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $command = new OroRequirejsBuildCommand('test');
        $this->assertEquals('oro:requirejs:build', $command->getName());
    }

    public function testGenerateBuildConfig()
    {
        $parameters = array(
            'oro_require_js' => array(
                'build_path' => 'js/test/app.min.js',
                'config_path' => 'js/test/require-config.js',
                'config' => array(
                    'paths' => array(),
                ),
                'build' => array(
                    'paths' => array(),
                ),
            ),
            'kernel.bundles' => array('Oro\Bundle\RequireJSBundle\Tests\Unit\Fixtures\TestBundle')
        );

        $expected = array(
            'paths' => array(
                'oro/test' => 'empty:',
                'require-config' => '../js/test/require-config',
                'require-lib' => 'ororequirejs/lib/require',
            ),
            'baseUrl' => './bundles',
            'out' => './js/test/app.min.js',
            'mainConfigFile' => './js/test/require-config.js',
            'include' => array(
                'require-config',
                'require-lib',
                'oro/test',
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

        $command = $this->getMockBuilder('Oro\Bundle\RequireJSBundle\Command\OroRequirejsBuildCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('getContainer'))
            ->getMock();
        $command->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $method = new \ReflectionMethod(
            'Oro\Bundle\RequireJSBundle\Command\OroRequirejsBuildCommand', 'generateBuildConfig');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($command));
    }
}
