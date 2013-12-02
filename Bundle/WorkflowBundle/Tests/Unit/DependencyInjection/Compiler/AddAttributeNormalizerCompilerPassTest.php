<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddAttributeNormalizerCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

class AddAttributeNormalizerCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $definition = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with(AddAttributeNormalizerCompilerPass::NORMALIZER_SERVICE)
            ->will($this->returnValue($definition));

        $services = array('testId' => array());
        $containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(AddAttributeNormalizerCompilerPass::ATTRIBUTE_NORMALIZER_TAG)
            ->will($this->returnValue($services));
        $definition->expects($this->once())
            ->method('addMethodCall')
            ->with('addAttributeNormalizer', array(new Reference('testId')));
        $pass = new AddAttributeNormalizerCompilerPass();
        $pass->process($containerBuilder);
    }
}
