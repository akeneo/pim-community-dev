<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class Select2Decorator extends ElementDecorator
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
        if ('' !== $operator && null !== $operator) {
            $operatorField = $this->decorate(
                $this->find('css', '.operator.select2-container'),
                ['Pim\Behat\Decorator\Field\Select2Decorator']
            );
            $operatorField->setValue($operator);
        }

        if ('' !== $value && null !== $value) {
            $valueField = $this->decorate(
                $this->find('css', '.value.select2-container'),
                ['Pim\Behat\Decorator\Field\Select2Decorator']
            );
            $valueField->setValue($value);
        }
    }
}
