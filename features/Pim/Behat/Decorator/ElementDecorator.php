<?php

namespace Pim\Behat\Decorator;

use Behat\Mink\Element\Element;

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

    /**
     * Decorates an element
     *
     * @param Element $element
     * @param array   $decorators
     *
     * @return ElementDecorator
     */
    protected function decorate(Element $element, array $decorators)
    {
        foreach ($decorators as $decorator) {
            $element = new $decorator($element);
        }

        return $element;
    }
}
