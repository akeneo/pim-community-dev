<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\FormBundle\DependencyInjection\Compiler\AutocompleteCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

class AutocompleteCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $attributes = array(
            'testId1' => array(
                array('alias' => 'tag1'), array('alias' => 'tag2')
            ),
            'testId2' => array(
                array('alias' => 'tag1', 'acl_resource' => 'test_acl_resource')
            ),
            'testId3' => array(
                array('name' => 'not_matched')
            )
        );

        $searchRegistryDefinition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $searchRegistryDefinition->expects($this->exactly(4))
            ->method('addMethodCall');
        $searchRegistryDefinition->expects($this->at(0))
            ->method('addMethodCall')
            ->with('addSearchHandler', array('tag1', new Reference('testId1')));
        $searchRegistryDefinition->expects($this->at(1))
            ->method('addMethodCall')
            ->with('addSearchHandler', array('tag2', new Reference('testId1')));
        $searchRegistryDefinition->expects($this->at(2))
            ->method('addMethodCall')
            ->with('addSearchHandler', array('tag1', new Reference('testId2')));
        $searchRegistryDefinition->expects($this->at(3))
            ->method('addMethodCall')
            ->with('addSearchHandler', array('testId3', new Reference('testId3')));

        $securityDefinition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $securityDefinition->expects($this->exactly(1))
            ->method('addMethodCall');
        $securityDefinition->expects($this->at(0))
            ->method('addMethodCall')
            ->with('setAutocompleteAclResource', array('tag1', 'test_acl_resource'));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->at(0))
            ->method('getDefinition')
            ->with('oro_form.autocomplete.search_registry')
            ->will($this->returnValue($searchRegistryDefinition));

        $container->expects($this->at(1))
            ->method('getDefinition')
            ->with('oro_form.autocomplete.security')
            ->will($this->returnValue($securityDefinition));

        $container->expects($this->at(2))
            ->method('findTaggedServiceIds')
            ->with('oro_form.autocomplete.search_handler')
            ->will($this->returnValue($attributes));

        $pass = new AutocompleteCompilerPass();
        $pass->process($container);
    }
}
