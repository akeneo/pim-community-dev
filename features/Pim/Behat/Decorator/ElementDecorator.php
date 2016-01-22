<?php

namespace Pim\Behat\Decorator;

use Behat\Mink\Element\NodeElement;

/**
 * Simple abstract class to ease the decorator pattern on Mink elements
 */
abstract class ElementDecorator
{
    /** @var mixed The decorated element */
    protected $element;

    /**
     * @param NodeElement $element
     */
    public function __construct(NodeElement $element)
    {
        $this->element = $element;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return call_user_func_array([$this->element, $name], $arguments);
    }
}
