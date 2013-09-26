<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Twig;

use Oro\Bundle\NavigationBundle\Twig\TitleNode;

class TitleNodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $node;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $compiler;

    /**
     * @var TitleNode
     */
    private $titleNode;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->node = $this->getMock('Twig_Node');
        $this->compiler = $this->getMockBuilder('Twig_Compiler')
            ->disableOriginalConstructor()
            ->getMock();

        $this->titleNode = new TitleNode($this->node);
    }

    /**
     * Tests error in twig tag call
     *
     * @expectedException \Twig_Error_Syntax
     */
    public function testFailedCompile()
    {
        $this->node->expects($this->once())->method('getIterator')->will($this->returnValue(array()));

        $this->titleNode->compile($this->compiler);
    }

    /**
     * Tests success node compiling
     */
    public function testSuccessCompile()
    {
        $firstExpr = $this->getMockBuilder('Twig_Node_Expression_Array')->disableOriginalConstructor()->getMock();
        $secondExpr = $this->getMockBuilder('Twig_Node_Expression_Array')->disableOriginalConstructor()->getMock();
        $thirdExpr = $this->getMockBuilder('Twig_Node_Expression_Array')->disableOriginalConstructor()->getMock();

        $this->node->expects($this->at(0))
            ->method('getIterator')
            ->will($this->returnValue(array($firstExpr)));

        $this->node->expects($this->at(1))
            ->method('getIterator')
            ->will($this->returnValue(array($secondExpr)));

        $this->node->expects($this->at(2))
            ->method('getIterator')
            ->will($this->returnValue(array($thirdExpr)));

        $firstFileName = 'file_one';
        $secondFileName = 'file_one';
        $thirdFileName = 'file_two';

        $at = 0;

        $this->compiler->expects($this->at($at++))
            ->method('getFilename')
            ->will($this->returnValue($firstFileName));

        $this->addExceptCompilerCalls($at, $firstExpr);

        $this->compiler->expects($this->at($at++))
            ->method('getFilename')
            ->will($this->returnValue($secondFileName));

        $this->addExceptCompilerCalls($at, $secondExpr);

        $this->compiler->expects($this->at($at++))
            ->method('getFilename')
            ->will($this->returnValue($thirdFileName));

        $this->titleNode->compile($this->compiler);
        $this->titleNode->compile($this->compiler);
        $this->titleNode->compile($this->compiler);
    }

    protected function addExceptCompilerCalls(&$at, $exprMock)
    {
        $this->compiler->expects($this->at($at++))
            ->method('raw')
            ->with("\n")
            ->will($this->returnSelf());

        $this->compiler->expects($this->at($at++))
            ->method('write')
            ->with('$this->env->getExtension("oro_title")->set(')
            ->will($this->returnSelf());

        $this->compiler->expects($this->at($at++))
            ->method('subcompile')
            ->with($exprMock)
            ->will($this->returnSelf());

        $this->compiler->expects($this->at($at++))
            ->method('raw')
            ->with(");\n")
            ->will($this->returnSelf());
    }
}
