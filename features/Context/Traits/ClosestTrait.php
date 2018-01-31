<?php

namespace Context\Traits;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;

trait ClosestTrait
{
    /**
     * This returns the first element matching a class.
     *
     * @param $node  NodeElement
     * @param $class string
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    protected function getClosest($node, $class)
    {
        $result = $node;
        while (!$result->hasClass($class)) {
            $result = $result->getParent();

            if (null === $result) {
                throw new ElementNotFoundException($this->getSession());
            }
        }

        return $result;
    }
}
