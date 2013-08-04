<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\EmailAddressConfigurationPass;

class EmailAddressConfigurationPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessNoServices()
    {
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->exactly(2))
            ->method('hasDefinition')
            ->will(
                $this->returnValueMap(
                    array(
                        array(EmailAddressConfigurationPass::EMAIL_ADDRESS_MANAGER_SERVICE_KEY, false),
                        array(EmailAddressConfigurationPass::EMAIL_OWNER_PROVIDER_SERVICE_KEY, false)
                    )
                )
            );
        $containerBuilder->expects($this->never())
            ->method('getDefinition');
        $containerBuilder->expects($this->never())
            ->method('findTaggedServiceIds');

        $pass = new EmailAddressConfigurationPass();
        $pass->process($containerBuilder);
    }

    public function testProcess()
    {
        $service1 = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $service2 = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->exactly(2))
            ->method('hasDefinition')
            ->will(
                $this->returnValueMap(
                    array(
                        array(EmailAddressConfigurationPass::EMAIL_ADDRESS_MANAGER_SERVICE_KEY, true),
                        array(EmailAddressConfigurationPass::EMAIL_OWNER_PROVIDER_SERVICE_KEY, true)
                    )
                )
            );
        $containerBuilder->expects($this->exactly(2))
            ->method('getDefinition')
            ->will(
                $this->returnValueMap(
                    array(
                        array(EmailAddressConfigurationPass::EMAIL_ADDRESS_MANAGER_SERVICE_KEY, $service1),
                        array(EmailAddressConfigurationPass::EMAIL_OWNER_PROVIDER_SERVICE_KEY, $service2)
                    )
                )
            );
        $containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->will(
                $this->returnValue(
                    array(
                        'provider1' => array(array('order' => 3)),
                        'provider2' => array(array('order' => 1)),
                        'provider4' => array(),
                        'provider3' => array(array('order' => 2)),
                    )
                )
            );

        $service1MethodCalls = array();
        $service1Providers = array();
        $service1->expects($this->exactly(4))
            ->method('addMethodCall')
            ->will(
                $this->returnCallback(
                    function ($method, array $arguments) use (&$service1MethodCalls, &$service1Providers) {
                        $service1MethodCalls[] = $method;
                        $service1Providers[] = (string)$arguments[0];
                    }
                )
            );

        $service2MethodCalls = array();
        $service2Providers = array();
        $service2->expects($this->exactly(4))
            ->method('addMethodCall')
            ->will(
                $this->returnCallback(
                    function ($method, array $arguments) use (&$service2MethodCalls, &$service2Providers) {
                        $service2MethodCalls[] = $method;
                        $service2Providers[] = (string)$arguments[0];
                    }
                )
            );

        $pass = new EmailAddressConfigurationPass();
        $pass->process($containerBuilder);

        $this->assertEquals(
            array('addProvider', 'addProvider', 'addProvider', 'addProvider'),
            $service1MethodCalls
        );
        $this->assertEquals(
            array('provider2', 'provider3', 'provider1', 'provider4'),
            $service1Providers
        );
        $this->assertEquals(
            array('addProvider', 'addProvider', 'addProvider', 'addProvider'),
            $service2MethodCalls
        );
        $this->assertEquals(
            array('provider2', 'provider3', 'provider1', 'provider4'),
            $service2Providers
        );
    }
}
