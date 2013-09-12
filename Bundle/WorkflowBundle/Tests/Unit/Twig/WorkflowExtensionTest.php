<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Twig;

use Oro\Bundle\WorkflowBundle\Twig\WorkflowExtension;

class WorkflowExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowRegistry;

    /**
     * @var WorkflowExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->workflowRegistry = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new WorkflowExtension($this->workflowRegistry);
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        /** @var \Twig_SimpleFunction $function */
        $function = current($functions);
        $this->assertInstanceOf('\Twig_SimpleFunction', $function);
        $this->assertEquals('has_workflows', $function->getName());
        $this->assertEquals(array($this->extension, 'hasWorkflows'), $function->getCallable());
    }

    public function testGetName()
    {
        $this->assertEquals(WorkflowExtension::NAME, $this->extension->getName());
    }

    /**
     * @dataProvider workflowsDataProvider
     * @param array $result
     * @param bool $expected
     */
    public function testHasWorkflows($result, $expected)
    {
        $class = '\stdClass';
        $this->workflowRegistry->expects($this->once())
            ->method('getWorkflowsByEntityClass')
            ->with($class)
            ->will($this->returnValue($result));

        $this->assertEquals($expected, $this->extension->hasWorkflows($class));
    }

    public function workflowsDataProvider()
    {
        return array(
            array(array(), false),
            array(null, false),
            array(array('test_workflow'), true)
        );
    }
}
