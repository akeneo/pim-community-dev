<?php
namespace Oro\Bundle\UIBundle\Twig\Node;

use \Twig_Compiler;
use \Twig_Node_Expression_Constant;

use \Twig_Node_Include;
use Symfony\Bundle\TwigBundle\Node\RenderNode;

class PositionNode extends \Twig_Node
{
    /**
     * @var array Array of blocks in the position
     */
    protected $blocks;

    protected $line;

    protected $tag;

    /**
     * @param array $blocks Array of blocks in the position
     * @param array $line Line
     * @param int   $tag twig tag
     */
    public function __construct(array $blocks, $line, $tag)
    {
        parent::__construct(array(), array('value' => $blocks), $line);
        $this->blocks = $blocks;
        $this->line = $line;
        $this->tag = $tag;
    }

    /**
     * {@inheritDoc}
     */
    public function compile(Twig_Compiler $compiler)
    {
        foreach ($this->blocks as $blockData) {
            if (array_key_exists('template', $blockData)) {
                $expr = new Twig_Node_Expression_Constant($blockData['template'], $this->line);
                $block = new Twig_Node_Include($expr, null, true, $this->line, $this->tag);
                $block->compile($compiler);
            } elseif (array_key_exists('action', $blockData)) {
                $expr = new Twig_Node_Expression_Constant($blockData['action'], $this->line);
                $attr = new Twig_Node_Expression_Constant(array(), $this->line);
                $block = new RenderNode($expr, $attr, $attr, $this->line, $this->tag);
                $block->compile($compiler);
            }
        }
    }
}