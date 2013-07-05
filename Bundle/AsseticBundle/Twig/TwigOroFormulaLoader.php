<?php

namespace Oro\Bundle\AsseticBundle\Twig;

use Assetic\Factory\Loader\FormulaLoaderInterface;
use Assetic\Factory\Resource\ResourceInterface;
use Oro\Bundle\AsseticBundle\Node\OroAsseticNode;
use Assetic\Extension\Twig\AsseticFilterFunction;
use Assetic\Extension\Twig\AsseticNode;

class TwigOroFormulaLoader implements FormulaLoaderInterface
{

    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function load(ResourceInterface $resource)
    {
        try {
            $tokens = $this->twig->tokenize($resource->getContent(), (string) $resource);
            $nodes  = $this->twig->parse($tokens);
        } catch (\Exception $e) {
            return array();
        }

        return $this->loadNode($nodes);
    }

    /**
     * Loads assets from the supplied node.
     *
     * @param \Twig_Node $node
     *
     * @return array An array of asset formulae indexed by name
     */
    private function loadNode(\Twig_Node $node)
    {
        $formulae = array();

        if ($node instanceof OroAsseticNode) {
            $inputs = $node->getAttribute('inputs');

            $formulae[$node->getAttribute('name')] = array(
                $inputs['uncompress'][0],
                $node->getAttribute('filters'),
                array(
                    'output'  => $node->getAttribute('compressAsset')->getTargetPath(),
                    'name'    => $node->getAttribute('name'),
                    'debug'   => $node->getAttribute('debug'),
                    'combine' => false,
                    'vars'    => $node->getAttribute('vars'),
                ),
            );
        }

        foreach ($node as $child) {
            if ($child instanceof \Twig_Node) {
                $formulae += $this->loadNode($child);
            }
        }

        return $formulae;
    }

}
