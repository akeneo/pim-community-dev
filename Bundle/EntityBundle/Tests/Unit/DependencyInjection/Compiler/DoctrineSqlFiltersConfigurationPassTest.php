<?php

namespace Oro\Bundle\EntityBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineSqlFiltersConfigurationPass;

class DoctrineSqlFiltersConfigurationPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $containerBuilder;

    /**
     * @var DoctrineSqlFiltersConfigurationPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->markTestSkipped(
            'Refactor tests after filter collection is refactored'
        );
        $this->pass = new DoctrineSqlFiltersConfigurationPass();
    }

    protected function prepareContainer()
    {
        $this->containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $this->containerBuilder->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo(DoctrineSqlFiltersConfigurationPass::FILTER_COLLECTION_SERVICE_NAME))
            ->will($this->returnValue(true));
    }

    /**
     *
     */
    public function testProcess()
    {
        $this->prepareContainer();
        $service = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo(DoctrineSqlFiltersConfigurationPass::FILTER_COLLECTION_SERVICE_NAME))
            ->will($this->returnValue($service));

        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo(DoctrineSqlFiltersConfigurationPass::TAG_NAME))
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

        $this->containerBuilder->expects($this->once())
            ->method('findDefinition')
            ->with('doctrine.orm.entity_manager')
            ->will($this->returnValue($em));

        $em->expects($this->once())->method('addMethodCall')
            ->with(
                'setFilterCollection',
                $this->containsOnlyInstancesOf('\Symfony\Component\DependencyInjection\Reference')
            );

        $this->pass->process($this->containerBuilder);
    }

    /**
     * @expectedException \LogicException
     */
    public function testProcessMissingFilterNameException()
    {
        $this->prepareContainer();
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo(DoctrineSqlFiltersConfigurationPass::TAG_NAME))
            ->will(
                $this->returnValue(
                    array(
                        'filter1' => array(array('enabled' => false)),
                    )
                )
            );

        $this->pass->process($this->containerBuilder);
    }

    /**
     * @expectedException \LogicException
     */
    public function testProcessDublicateFilterNameException()
    {
        $this->prepareContainer();
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo(DoctrineSqlFiltersConfigurationPass::TAG_NAME))
            ->will(
                $this->returnValue(
                    array(
                        'filter1' => array(array('filter_name' => 'filter_name1', 'enabled' => false)),
                        'filter2' => array(array('filter_name' => 'filter_name1', 'enabled' => true)),
                    )
                )
            );

        $this->pass->process($this->containerBuilder);
    }
}
