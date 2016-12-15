<?php

namespace Context\Traits;

use Behat\Mink\Element\NodeElement;

trait ClosestTrait
{
    /**
     * This returns the first element matching a class.
     *
     * @param $node  NodeElement
     * @param $class string
     *
     * @return NodeElement
     */
    protected function getClosest($node, $class)
    {
        $result = $node->getParent();
        while (!$result->hasClass($class)) {
            $result = $result->getParent();
        }

        return $result;
    }
}
