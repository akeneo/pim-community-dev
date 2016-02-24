<?php

namespace Pim\Behat\Decorator\TreeDecorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Js tree lib Decorator to ease the dom manipulation and assertion arround it.
 */
class JsTreeDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $nodeName
     *
     * @return NodeElement
     */
    public function findNodeInTree($nodeName)
    {
        return $this->spin(function () use ($nodeName) {
            return $this->find('css', sprintf('li a:contains("%s")', $nodeName));
        }, sprintf('Cannot find the node "%s"', $nodeName));
    }

    /**
     * @param string $nodeName
     */
    public function expandNode($nodeName)
    {
        $node = $this->findNodeInTree($nodeName)->getParent();
        if ($node->hasClass('jstree-closed')) {
            $nodeElement = $this->spin(function () use ($node) {
                return $node->find('css', 'ins');
            });

            $nodeElement->click();
        }
    }

    /**
     * @param string $treeName
     */
    public function selectTree($treeName)
    {
        $this->spin(function () use ($treeName) {
            if (null !== $treeSelect = $this->find('css', '#tree_select')) {
                $treeSelect->selectOption($treeName);

                return true;
            }

            $link = $this->find('css', sprintf('#trees-list li a:contains("%s")', $treeName));

            if (null === $link) {
                return false;
            }

            $link->click();

            return true;
        }, sprintf('Tree "%s" not found', $treeName));
    }
}
