<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit;

use Oro\Bundle\WorkflowBundle\OroWorkflowBundle;

class OroWorkflowBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('addCompilerPass'))
            ->getMock();

        $containerBuilder->expects($this->at(0))
            ->method('addCompilerPass')
            ->with(
                $this->isInstanceOf(
                    'Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddConditionAndActionCompilerPass'
                )
            );

        $containerBuilder->expects($this->at(1))
            ->method('addCompilerPass')
            ->with(
                $this->isInstanceOf(
                    'Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddAttributeNormalizerCompilerPass'
                )
            );

        $bundle = new OroWorkflowBundle();
        $bundle->build($containerBuilder);
    }
}
