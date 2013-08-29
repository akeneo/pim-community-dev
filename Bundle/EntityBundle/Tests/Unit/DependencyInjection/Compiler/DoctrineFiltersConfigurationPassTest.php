<?php

namespace Oro\Bundle\EntityBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineFiltersConfigurationPass;

class DoctrineFiltersConfigurationPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo(DoctrineFiltersConfigurationPass::FILTERS_SERVICE_KEY))
            ->will($this->returnValue(true));

        $service = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo(DoctrineFiltersConfigurationPass::FILTERS_SERVICE_KEY))
            ->will($this->returnValue($service));

        $containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo(DoctrineFiltersConfigurationPass::TAG))
            ->will(
                $this->returnValue(
                    array(
                        'filter1' => array(array('filter_name' => 'filter_name1', 'enabled' => false)),
                        'filter2' => array(array('filter_name' => 'filter_name2', 'enabled' => true)),
                    )
                )
            );

        $service->expects($this->at(0))->method('addMethodCall')->with('addFilter', $this->contains('filter_name1'));
        $service->expects($this->at(1))->method('addMethodCall')->with('addFilter', $this->contains('filter_name2'));
        $service->expects($this->at(2))->method('addMethodCall')->with('enable', array('filter_name2'));

        $em = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $containerBuilder->expects($this->once())
            ->method('findDefinition')
            ->with('doctrine.orm.entity_manager')
            ->will($this->returnValue($em));

        $em->expects($this->once())->method('addMethodCall')
            ->with(
                'setFilterCollection',
                $this->containsOnlyInstancesOf('\Symfony\Component\DependencyInjection\Reference')
            );

        $pass = new DoctrineFiltersConfigurationPass();
        $pass->process($containerBuilder);
    }
}
