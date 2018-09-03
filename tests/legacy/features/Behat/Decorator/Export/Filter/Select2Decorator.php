<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator as Select2DecoratorField;

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
        if (null !== $operator && '' !== $operator) {
            $operatorField = $this->spin(function () {
                return $this->find('css', '.select2-container.operator');
            }, 'Cannot find the operator field');

            $operatorField = $this->decorate(
                $operatorField,
                [Select2DecoratorField::class]
            );
            $operatorField->setValue($operator);
        }

        if (null !== $value && '' !== $value) {
            $valueField = $this->decorate(
                $this->find('css', '.value.select2-container'),
                [Select2DecoratorField::class]
            );
            $valueField->setValue($value);

            $this->getSession()->executeScript(
                sprintf('$(\'.filter-item[data-name="%s"][data-type="%s"] [name="filter-value"]\').trigger(\'change\')', $this->getAttribute('data-name'), $this->getAttribute('data-type'))
            );
        }
    }
}
