<?php

namespace Pim\Behat\Decorator\TreeDecorator;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Js tree lib Decorator to ease the dom manipulation and assertion around it.
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
            return $this->find('css', sprintf('li[data-code="%s"]', $nodeName));
        }, sprintf('Cannot find the node "%s"', $nodeName));
    }

    /**
     * @param string $nodeName
     */
    public function expandNode($nodeName)
    {
        $node = $this->findNodeInTree($nodeName);
        if ($node->hasClass('jstree-closed')) {
            $nodeElement = $this->spin(function () use ($node) {
                return $node->find('css', 'ins');
            });

            $nodeElement->click();
            $this->spin(function () use ($node) {
                return null === $node->find('css', 'a.jstree-loading');
            }, sprintf('Cannot open the node "%s"', $nodeName));
        }
    }
}
