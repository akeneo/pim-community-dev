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
        $compilerClass = 'Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddConditionAndPostActionCompilerPass';
        $containerBuilder->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf($compilerClass));

        $bundle = new OroWorkflowBundle();
        $bundle->build($containerBuilder);
    }
}
