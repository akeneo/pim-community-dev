<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Twig;

use Oro\Bundle\WorkflowBundle\Twig\ViewAttributeExtension;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class ViewAttributeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextAccessor;

    /**
     * @var ViewAttributeExtension
     */
    protected $extension;

    /**
     * @var string[]
     */
    protected $templateNames;

    protected function setUp()
    {
        $this->contextAccessor = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\ContextAccessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->templateNames = array(
            'TestBundle:Workflow:template_one.html.twig',
            'TestBundle:Workflow:template_two.html.twig'
        );

        $this->extension = new ViewAttributeExtension($this->contextAccessor, $this->templateNames);
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(3, $functions);

        $this->assertInstanceOf('\Twig_SimpleFunction', $functions[0]);
        $this->assertEquals('oro_workflow_render_view_attribute_row', $functions[0]->getName());
        $this->assertEquals(array($this->extension, 'renderViewAttributeRow'), $functions[0]->getCallable());

        $this->assertInstanceOf('\Twig_SimpleFunction', $functions[1]);
        $this->assertEquals('oro_workflow_render_view_attribute_value', $functions[1]->getName());
        $this->assertEquals(array($this->extension, 'renderViewAttributeValue'), $functions[1]->getCallable());

        $this->assertInstanceOf('\Twig_SimpleFunction', $functions[2]);
        $this->assertEquals('oro_workflow_get_value_by_path', $functions[2]->getName());
        $this->assertEquals(array($this->extension, 'getValueByPropertyPath'), $functions[2]->getCallable());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_workflow_view_attribute', $this->extension->getName());
    }

    /**
     * @dataProvider renderViewAttributeBlockExceptionDataProvider
     */
    public function testRenderViewAttributeBlockNotFoundException(
        $renderMethod,
        $blockName,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $environment = $this->getMock('Twig_Environment');

        $templateOne = $this->getMockBuilder('Twig_Template')
            ->disableOriginalConstructor()
            ->setMethods(array('hasBlock'))
            ->getMockForAbstractClass();

        $templateTwo = $this->getMockBuilder('Twig_Template')
            ->disableOriginalConstructor()
            ->setMethods(array('hasBlock'))
            ->getMockForAbstractClass();

        $workflowItem = new WorkflowItem();
        $viewAttribute = array('path' => '$testAttribute', 'label' => 'Test Attribute', 'view_type' => 'test');

        $environment->expects($this->at(0))
            ->method('loadTemplate')
            ->with('TestBundle:Workflow:template_two.html.twig')
            ->will($this->returnValue($templateTwo));

        $environment->expects($this->at(1))
            ->method('loadTemplate')
            ->with('TestBundle:Workflow:template_one.html.twig')
            ->will($this->returnValue($templateOne));

        $templateTwo->expects($this->at(0))
            ->method('hasBlock')
            ->with($viewAttribute['view_type'] . '_' . $blockName)
            ->will($this->returnValue(false));

        $templateTwo->expects($this->at(1))
            ->method('hasBlock')
            ->with($blockName)
            ->will($this->returnValue(false));

        $templateOne->expects($this->at(0))
            ->method('hasBlock')
            ->with($viewAttribute['view_type'] . '_' . $blockName)
            ->will($this->returnValue(false));

        $templateOne->expects($this->at(1))
            ->method('hasBlock')
            ->with($blockName)
            ->will($this->returnValue(false));

        $this->setExpectedException($expectedException, $expectedExceptionMessage);

        $this->extension->$renderMethod($environment, $workflowItem, $viewAttribute);
    }

    public function renderViewAttributeBlockExceptionDataProvider()
    {
        return array(
            array(
                'renderMethod' => 'renderViewAttributeRow',
                'blockName' => 'workflow_view_attribute_row',
                'expectedException' => 'RuntimeException',
                'expectedExceptionMessage' =>
                    'Cannot find view attribute block "workflow_view_attribute_row" in templates '
                    . '"TestBundle:Workflow:template_one.html.twig", "TestBundle:Workflow:template_two.html.twig".',
            ),
            array(
                'renderMethod' => 'renderViewAttributeValue',
                'blockName' => 'workflow_view_attribute_value',
                'expectedException' => 'RuntimeException',
                'expectedExceptionMessage' =>
                    'Cannot find view attribute block "workflow_view_attribute_value" in templates '
                    . '"TestBundle:Workflow:template_one.html.twig", "TestBundle:Workflow:template_two.html.twig".',
            ),
        );
    }

    /**
     * @dataProvider renderViewAttributeBlockRequiredOptionExceptionDataProvider
     */
    public function testRenderViewAttributeBlockRequiredOptionException(
        $renderMethod,
        $viewAttribute,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $environment = $this->getMock('Twig_Environment');
        $workflowItem = new WorkflowItem();
        $environment->expects($this->never())->method($this->anything());
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->extension->$renderMethod($environment, $workflowItem, $viewAttribute);
    }

    public function renderViewAttributeBlockRequiredOptionExceptionDataProvider()
    {
        return array(
            array(
                'renderMethod' => 'renderViewAttributeRow',
                'viewAttribute' => array('label' => 'Test'),
                'expectedException' => 'InvalidArgumentException',
                'expectedExceptionMessage' => 'Option "path" is not found in view attribute.',
            ),
            array(
                'renderMethod' => 'renderViewAttributeValue',
                'viewAttribute' => array('label' => 'Test'),
                'expectedException' => 'InvalidArgumentException',
                'expectedExceptionMessage' => 'Option "path" is not found in view attribute.',
            ),
            array(
                'renderMethod' => 'renderViewAttributeRow',
                'viewAttribute' => array('path' => '$test'),
                'expectedException' => 'InvalidArgumentException',
                'expectedExceptionMessage' => 'Option "label" is not found in view attribute.',
            ),
            array(
                'renderMethod' => 'renderViewAttributeValue',
                'viewAttribute' => array('path' => '$test'),
                'expectedException' => 'InvalidArgumentException',
                'expectedExceptionMessage' => 'Option "label" is not found in view attribute.',
            ),
        );
    }

    /**
     * @dataProvider renderViewAttributeBlockDataProvider
     */
    public function testRenderViewAttributeBlock(
        $renderMethod,
        $blockName,
        $viewAttribute,
        $expectedBlockNamesFallback
    ) {
        $environment = $this->getMock('Twig_Environment');

        /** @var \Twig_Template $templateOne */
        $templateOne = $this->getMockBuilder('Twig_Template')
            ->disableOriginalConstructor()
            ->setMethods(array('hasBlock', 'renderBlock'))
            ->getMockForAbstractClass();
        $templateOne->name = 'one';

        $templateTwo = $this->getMockBuilder('Twig_Template')
            ->disableOriginalConstructor()
            ->setMethods(array('hasBlock', 'renderBlock'))
            ->getMockForAbstractClass();
        $templateTwo->name = 'two';

        $workflowItem = new WorkflowItem();

        $expectedOutput = 'Test Attribute: test';
        $expectedValue = isset($viewAttribute['path']) ? 'test' : null;
        $expectedBlockName = $blockName;
        $expectedViewAttribute = array_merge(
            $viewAttribute,
            array(
                'value' => $expectedValue,
                'workflow_item' => $workflowItem,
            )
        );

        $this->contextAccessor->expects($this->at(0))
            ->method('getValue')
            ->with($workflowItem, $viewAttribute['path'])
            ->will($this->returnValue($expectedValue));

        $environment->expects($this->at(0))
            ->method('loadTemplate')
            ->with('TestBundle:Workflow:template_two.html.twig')
            ->will($this->returnValue($templateTwo));

        $environment->expects($this->at(1))
            ->method('loadTemplate')
            ->with('TestBundle:Workflow:template_one.html.twig')
            ->will($this->returnValue($templateOne));

        foreach ($expectedBlockNamesFallback as $index => $blockName) {
            $templateTwo->expects($this->at($index))
                ->method('hasBlock')
                ->with($blockName)
                ->will($this->returnValue(false));

            $templateOne->expects($this->at($index))
                ->method('hasBlock')
                ->with($blockName)
                ->will($this->returnValue($index == count($expectedBlockNamesFallback) - 1)); // last iteration
        }

        $templateOne->expects($this->at(++$index))
            ->method('renderBlock')
            ->with($expectedBlockName, $expectedViewAttribute)
            ->will($this->returnValue($expectedOutput));

        $this->assertEquals(
            $expectedOutput,
            $this->extension->$renderMethod($environment, $workflowItem, $viewAttribute)
        );

        $this->contextAccessor->expects($this->at(0))
            ->method('getValue')
            ->with($workflowItem, $viewAttribute['path'])
            ->will($this->returnValue($expectedValue));

        $templateOne->expects($this->at(0))
            ->method('renderBlock')
            ->with($expectedBlockName, $expectedViewAttribute)
            ->will($this->returnValue($expectedOutput));

        $this->assertEquals(
            $expectedOutput,
            $this->extension->$renderMethod($environment, $workflowItem, $viewAttribute)
        );
    }

    public function renderViewAttributeBlockDataProvider()
    {
        return array(
            array(
                'renderMethod' => 'renderViewAttributeRow',
                'blockName' => 'workflow_view_attribute_row',
                'viewAttribute' => array(
                    'path' => '$testAttribute', 'label' => 'Test Attribute', 'view_type' => 'test'
                ),
                'expectedBlockNamesFallback' =>
                    array('test_workflow_view_attribute_row', 'workflow_view_attribute_row')
            ),
            array(
                'renderMethod' => 'renderViewAttributeValue',
                'blockName' => 'workflow_view_attribute_value',
                'viewAttribute' => array(
                    'path' => '$testAttribute', 'label' => 'Test Attribute', 'view_type' => 'test'
                ),
                'expectedBlockNamesFallback' =>
                    array('test_workflow_view_attribute_value', 'workflow_view_attribute_value')
            )
        );
    }
}
