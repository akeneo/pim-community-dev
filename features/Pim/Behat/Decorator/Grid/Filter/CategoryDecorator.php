<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class CategoryDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Opens the filter
     */
    public function open()
    {

    }

    /**
     * Sets operator and value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        if ('unclassified' === $operator) {
            $node = $this->spin(function () {
                return $this->find('css', '#node_-1 a');
            }, 'Could not find unclassified category filter');

            $node->click();
        } else {
            $values = '' !== $value ? explode(', ', $value) : [];

            foreach ($values as $value) {
                $this->findNodeInTree($value)->find('css', 'a')->click();
            }
        }
    }
}
