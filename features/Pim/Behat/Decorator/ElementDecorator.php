<?php

namespace Pim\Behat\Decorator;

/**
 * Simple abstract class to ease the decorator pattern on Mink elements
 */
abstract class ElementDecorator
{
    /** @var mixed The decorated element */
    protected $element;

    /**
     * @param $element
     */
    public function __construct($element)
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
