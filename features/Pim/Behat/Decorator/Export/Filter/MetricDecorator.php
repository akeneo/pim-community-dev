<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class MetricDecorator extends ElementDecorator
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
        $operatorField = $this->spin(function () {
            return $this->find('css', '.select2-container.operator');
        }, 'Cannot find the operator field');

        $operatorField = $this->decorate(
            $operatorField,
            ['Pim\Behat\Decorator\Field\Select2Decorator']
        );
        $operatorField->setValue($operator);

        if ('' !== $value) {
            list($data, $unit) = explode(' ', $value);

            $field = $this->find('css', '[name="filter-data"]');
            $field->setValue($data);
            $this->getSession()->executeScript(
                sprintf('$(\'.filter-item[data-name="%s"][data-type="%s"] [name="filter-data"]\').trigger(\'change\')', $this->getAttribute('data-name'), $this->getAttribute('data-type'))
            );

            $unitField = $this->spin(function () {
                return $this->find('css', '.select2-container.unit');
            }, 'Cannot find the operator field');

            $unitField = $this->decorate(
                $unitField,
                ['Pim\Behat\Decorator\Field\Select2Decorator']
            );

            $unitField->setValue($unit);
            $this->getSession()->executeScript(
                sprintf('$(\'.filter-item[data-name="%s"][data-type="%s"] select[name="filter-unit"]\').trigger(\'change\')', $this->getAttribute('data-name'), $this->getAttribute('data-type'))
            );
        }
    }
}
