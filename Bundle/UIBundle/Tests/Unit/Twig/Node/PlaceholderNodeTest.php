<?php
namespace Oro\Bundle\UIBundle\Tests\Unit\Twig\Node;

use Oro\Bundle\UIBundle\Twig\Node\PlaceholderNode;

class PlaceholderNodeTest extends \PHPUnit_Framework_TestCase
{
    protected $compiler;

    protected $blocks;
    protected $variables;
    protected $wrapClassName;
    protected $line;
    protected $tag;

    /**
     * @var \Oro\Bundle\UIBundle\Twig\Node\PlaceholderNode
     */
    protected $node;

    public function setUp()
    {
        $this->compiler = $this->getMockBuilder('Twig_Compiler')
            ->disableOriginalConstructor()
            ->getMock();

        $this->blocks = array(
            'items' => array(
                'some_action' => array(
                    array(
                        'action' => 'some_action'
                    ),
                ),
                'some_template' =>array(
                    array(
                        'template' => 'some_template'
                    )
                )
            )

        );

        $this->line = array(12);
        $this->variables = new \Twig_Node_Expression_Constant(array(), $this->line);
        $this->wrapClassName = 'test_class';

        $this->tag = 'test_tag';

        $this->node = new PlaceholderNode(
            $this->blocks,
            $this->variables,
            $this->wrapClassName,
            $this->line,
            $this->tag
        );
    }

    public function testCompile()
    {
        $this->compiler->expects($this->any())
            ->method('write')
            ->will($this->returnValue($this->compiler));

        $this->compiler->expects($this->any())
            ->method('subcompile')
            ->will($this->returnValue($this->compiler));

        $this->compiler->expects($this->any())
            ->method('outdent')
            ->will($this->returnValue($this->compiler));

        $this->compiler->expects($this->any())
            ->method('indent')
            ->will($this->returnValue($this->compiler));

        $this->compiler->expects($this->any())
            ->method('addDebugInfo')
            ->will($this->returnValue($this->compiler));

        $this->compiler->expects($this->any())
            ->method('raw')
            ->will($this->returnValue($this->compiler));

        $this->node->compile($this->compiler);

        $nodeWoVariables = new PlaceholderNode(
            array('items' => array('some_action' => array('action' => 'some_action'))),
            null,
            $this->wrapClassName,
            $this->line,
            $this->tag
        );
        $nodeWoVariables->compile($this->compiler);
    }
}
