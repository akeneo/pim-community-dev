<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\FormBundle\DependencyInjection\Compiler\SearchFactoryCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

class SearchFactoryCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $attributes = array(
            'testId1' => array(
                array('alias' => 'tag1'), array('alias' => 'tag2')
            ),
            'testId2' => array(
                array('alias' => 'tag1')
            ),
            'testId3' => array(
                array('name' => 'not_matched')
            )
        );

        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $definition->expects($this->exactly(4))
            ->method('addMethodCall');
        $definition->expects($this->at(0))
            ->method('addMethodCall')
            ->with('addSearchFactory', array('tag1', new Reference(new Reference('testId1'))));
        $definition->expects($this->at(1))
            ->method('addMethodCall')
            ->with('addSearchFactory', array('tag2', new Reference(new Reference('testId1'))));
        $definition->expects($this->at(2))
            ->method('addMethodCall')
            ->with('addSearchFactory', array('tag1', new Reference(new Reference('testId2'))));
        $definition->expects($this->at(3))
            ->method('addMethodCall')
            ->with('addSearchFactory', array('testId3', new Reference(new Reference('testId3'))));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->once())
            ->method('getDefinition')
            ->with('oro_form.autocomplete.search_factory')
            ->will($this->returnValue($definition));
        $container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('oro_form.autocomplete.search_factory')
            ->will($this->returnValue($attributes));

        $pass = new SearchFactoryCompilerPass();
        $pass->process($container);
    }
}
