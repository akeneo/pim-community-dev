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

    protected $variables;

    protected $wrapClassName;

    protected $line;

    protected $tag;

    /**
     * @param array $blocks Array of blocks in the position
     * @param       $variables
     * @param string $wrapClassName
     * @param array $line Line
     * @param int   $tag twig tag
     */
    public function __construct(array $blocks, $variables, $wrapClassName, $line, $tag)
    {
        parent::__construct(array(), array('value' => $blocks), $line);
        $this->blocks = $blocks;
        $this->wrapClassName = $wrapClassName;
        $this->line = $line;
        $this->tag = $tag;
        $this->variables = $variables;
    }

    /**
     * {@inheritDoc}
     */
    public function compile(Twig_Compiler $compiler)
    {
        foreach ($this->blocks as $blockData) {
            //$compiler->raw('echo \'<div id = "block-' . $blockData['name'] . '" class="' . $this->wrapClassName . '" >\';');
            if (array_key_exists('template', $blockData)) {
                $expr = new Twig_Node_Expression_Constant($blockData['template'], $this->line);
                $block = new Twig_Node_Include($expr, $this->variables, true, $this->line, $this->tag);
                $block->compile($compiler);
            } elseif (array_key_exists('action', $blockData)) {
                $expr = new Twig_Node_Expression_Constant($blockData['action'], $this->line);
                $attr = new Twig_Node_Expression_Constant(array(), $this->line);
                if ($this->variables == null) {
                    $attributes = $attr;
                } else {
                    $attributes = $this->variables;
                }
                $block = new RenderNode($expr, $attributes, $attr, $this->line, $this->tag);
                $block->compile($compiler);
            }
            //$compiler->raw('echo \'</div>\';');
        }
    }
}