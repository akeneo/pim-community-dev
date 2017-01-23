<?php

namespace Pim\Behat\Decorator\Tree;

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
        $node = $this->spin(function () use ($nodeName) {
            return $this->find('css', sprintf('li[data-code="%s"]', $nodeName));
        }, sprintf('Cannot find the node "%s"', $nodeName));

        return $this->decorate($node, ['Pim\Behat\Decorator\Tree\JsNodeDecorator']);
    }

    /**
     * This method is spinned because the refresh of the tree result in a WebDriver\Exception\NoSuchElement
     * exception if the tree was found then immediately refreshed.
     *
     * @param string $nodeName
     */
    public function expandNode($nodeName)
    {
        $this->spin(function () use ($nodeName) {
            $node = $this->findNodeInTree($nodeName);
            if (!$node->isOpen()) {
                $node->open();
            }

            return true;
        }, sprintf('Unable to expand node %s', $nodeName));
    }
}
