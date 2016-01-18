<?php

namespace Pim\Behat\Decorator\TreeDecorator;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Js tree lib Decorator to ease the dom manipulation and assertion arround it.
 */
class JsTreeDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param NodeElement $rootElement
     * @param string      $nodeName
     *
     * @return NodeElement
     */
    public function findNodeInTree($nodeName)
    {
        $node = $this->spin(function () use ($rootElement, $nodeName) {
            return $this->element->find('css', sprintf('li a:contains("%s")', $nodeName));
        }, sprintf('Unable to find node "%s" in the tree', $nodeName));

        return $node;
    }

    /**
     * @param string $nodeName
     *
     * @return Edit
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
}
