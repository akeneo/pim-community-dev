<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\EmailOwnerConfigurationPass;

class EmailOwnerConfigurationPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessNoServices()
    {
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo(EmailOwnerConfigurationPass::SERVICE_KEY))
            ->will($this->returnValue(false));
        $containerBuilder->expects($this->never())
            ->method('getDefinition');
        $containerBuilder->expects($this->never())
            ->method('findTaggedServiceIds');

        $pass = new EmailOwnerConfigurationPass();
        $pass->process($containerBuilder);
    }

    public function testProcess()
    {
        $service = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrineTargetEntityResolver = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->exactly(2))
            ->method('hasDefinition')
            ->will(
                $this->returnValueMap(
                    array(
                        array(EmailOwnerConfigurationPass::SERVICE_KEY, true),
                        array('doctrine.orm.listeners.resolve_target_entity', true),
                    )
                )
            );
        $containerBuilder->expects($this->exactly(2))
            ->method('getDefinition')
            ->will(
                $this->returnValueMap(
                    array(
                        array(EmailOwnerConfigurationPass::SERVICE_KEY, $service),
                        array('doctrine.orm.listeners.resolve_target_entity', $doctrineTargetEntityResolver),
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

        $serviceMethodCalls = array();
        $serviceProviders = array();
        $service->expects($this->exactly(4))
            ->method('addMethodCall')
            ->will(
                $this->returnCallback(
                    function ($method, array $arguments) use (&$serviceMethodCalls, &$serviceProviders) {
                        $serviceMethodCalls[] = $method;
                        $serviceProviders[] = (string)$arguments[0];
                    }
                )
            );

        $containerBuilder->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('oro_email.entity.cache_namespace'))
            ->will($this->returnValue('SomeNamespace'));

        $doctrineTargetEntityResolver->expects($this->once())
            ->method('addMethodCall')
            ->with(
                $this->equalTo('addResolveTargetEntity'),
                $this->equalTo(
                    array(
                        'Oro\Bundle\EmailBundle\Entity\EmailAddress',
                        'SomeNamespace\EmailAddressProxy',
                        array()
                    )
                )
            );

        $pass = new EmailOwnerConfigurationPass();
        $pass->process($containerBuilder);

        $this->assertEquals(
            array('addProvider', 'addProvider', 'addProvider', 'addProvider'),
            $serviceMethodCalls
        );
        $this->assertEquals(
            array('provider2', 'provider3', 'provider1', 'provider4'),
            $serviceProviders
        );
    }
}
