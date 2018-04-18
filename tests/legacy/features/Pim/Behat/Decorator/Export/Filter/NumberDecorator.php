<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class NumberDecorator extends ElementDecorator
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
        $operatorField = $this->decorate(
            $this->find('css', '.select2-container.operator'),
            ['Pim\Behat\Decorator\Field\Select2Decorator']
        );
        $operatorField->setValue($operator);

        if (null !== $value && '' !== $value) {
            $fieldValue = $this->spin(function () {
                return $this->find('css', '.value');
            }, 'Field value for number not found.');
            $fieldValue->setValue($value);
        }
    }
}
