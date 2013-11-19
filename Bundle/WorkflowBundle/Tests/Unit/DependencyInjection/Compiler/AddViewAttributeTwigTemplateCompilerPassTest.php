<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddViewAttributeTwigTemplateCompilerPass;

class AddViewAttributeTwigTemplateCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $templateName = 'TestBundle:Workflow:view_attributes.html.twig';
        $actualTemplates = array('OroWorkflowBundle:WorkflowStep:view_attributes.html.twig');
        $expectedTemplates = array('OroWorkflowBundle:WorkflowStep:view_attributes.html.twig', $templateName);

        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerBuilder->expects($this->once())
            ->method('getParameter')
            ->with(AddViewAttributeTwigTemplateCompilerPass::VIEW_ATTRIBUTES_TEMPLATES_PARAMETER)
            ->will($this->returnValue($actualTemplates));

        $containerBuilder->expects($this->once())
            ->method('setParameter')
            ->with(AddViewAttributeTwigTemplateCompilerPass::VIEW_ATTRIBUTES_TEMPLATES_PARAMETER, $expectedTemplates);

        $pass = new AddViewAttributeTwigTemplateCompilerPass($templateName);
        $pass->process($containerBuilder);
    }
}
