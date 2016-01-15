<?php

namespace Pim\Behat\Decorator;

use Behat\Mink\Element\NodeElement;

abstract class ElementDecorator
{
    protected $element;

    public function construct(NodeElement $element)
    {
        $this->element = $element;
    }

    public function __call (string $name, array $arguments)
    {
        if (!method_exists($this->elements, $name)) {
            return call_user_func_array(array($this->elements, $name), $arguments);
        } else {
            throw new \InvalidArgumentException(sprintf('No method found called %s on this element', $name));
        }
    }
}
