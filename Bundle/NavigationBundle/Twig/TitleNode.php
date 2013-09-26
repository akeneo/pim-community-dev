<?php

namespace Oro\Bundle\NavigationBundle\Twig;

/**
 * Class TitleNode
 * @package Oro\Bundle\NavigationBundle\Twig
 */
class TitleNode extends \Twig_Node
{
    /**
     * @var string|null
     */
    static protected $firstFileName = null;

    public function __construct(\Twig_Node $expr = null, $lineno = 0, $tag = null)
    {
        parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
    }

    /**
     * Compile title node to template
     *
     * @param  \Twig_Compiler     $compiler
     * @throws \Twig_Error_Syntax
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $node = $this->getNode('expr');

        $arguments = null;

        $nodes = $node->getIterator();

        // take first argument array node
        foreach ($nodes as $childNode) {
            if ($childNode instanceof \Twig_Node_Expression_Array) {
                $arguments = $childNode;

                break;
            }
        }

        if (is_null($arguments)) {
            throw new \Twig_Error_Syntax('Function oro_title_set expected argument: array');
        }

        if ($this->isSetTitleShouldBeProcessed($compiler)) {
            $compiler
                ->raw("\n")
                ->write('$this->env->getExtension("oro_title")->set(')
                ->subcompile($arguments)
                ->raw(");\n");
        }
    }

    /**
     * Compiler visits files in order from children to parents, to implement behaviour of extend titles in children
     * view files we should handle only nodes from first file, all others are overridden with first.
     *
     * @param \Twig_Compiler $compiler
     * @return bool
     */
    protected function isSetTitleShouldBeProcessed(\Twig_Compiler $compiler)
    {
        if (!self::$firstFileName) {
            self::$firstFileName = $compiler->getFilename();
            return true;
        } elseif (self::$firstFileName == $compiler->getFilename()) {
            return true;
        } else {
            return false;
        }
    }
}
