<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Compiler\EntityConfigPass;

class EntityConfigPassTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntityConfigPass */
    protected $compiler;

    /** @var ContainerBuilder */
    protected $builder;

    protected $config = array('oro_entity_config.provider' => array(
        0 => array('scope' => 'datagrid')
    ));

    /** Setup */
    protected function setup()
    {
        $this->compiler = new EntityConfigPass();
        $this->builder = new ContainerBuilder();
    }

    public function testProcess()
    {
        $this->setDefinitions();

        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with('oro_entity_config.provider')
            ->will($this->returnValue($this->config));

        $containerMock->expects($this->any())
            ->method('hasDefinition')
            ->with('oro_entity_config.entity_config.datagrid')
            ->will($this->returnValue(true));

        $containerMock->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue($this->builder->getDefinition('oro_grid.config.datagrid_config_provider')));

        $compilerPass = new EntityConfigPass();
        $compilerPass->process($containerMock);
    }

    public function testWarning()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with('oro_entity_config.provider')
            ->will($this->returnValue(array('oro_entity_config.provider' => array())));

        $this->setExpectedException('\Oro\Bundle\EntityConfigBundle\Exception\RuntimeException');

        $compilerPass = new EntityConfigPass();
        $compilerPass->process($containerMock);

    }

    public function testException()
    {
        $containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $containerMock->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with('oro_entity_config.provider')
            ->will($this->returnValue($this->config));

        $this->setExpectedException('\Oro\Bundle\EntityConfigBundle\Exception\RuntimeException');

        $compilerPass = new EntityConfigPass();
        $compilerPass->process($containerMock);
    }

    protected function setDefinitions()
    {
        $defRegistry_0 = new Definition('Oro\Bundle\EntityConfigBundle\ConfigManager');
        $defRegistry_1 = new Definition('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider');
        $definitions = array(
            'oro_entity_config.config_manager'          => $defRegistry_0,
            'oro_grid.config.datagrid_config_provider'  => $defRegistry_1
        );

        $this->builder->setDefinitions($definitions);
    }
}
