<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class BooleanDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Sets operator and value in the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function filter($operator, $value)
    {
        $field = $this->spin(function () {
            return $this->find('css', '.select2-container.value');
        }, sprintf('Cannot find the value field for the filter "%s"', $this->getAttribute('data-name')));

        $mapping = [
            'false' => 'disabled',
            'true'  => 'enabled',
            ''      => 'all'
        ];

        $field->setValue($mapping[$value]);
    }
}
