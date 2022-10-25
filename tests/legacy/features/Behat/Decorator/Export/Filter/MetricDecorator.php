<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * Decorator dedicated to metric attribute type.
 */
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
            return $this->element->find('css', '.select2-container.operator');
        }, 'Cannot find the metric operator field');

        $operatorField = $this->decorate(
            $operatorField,
            [Select2Decorator::class]
        );
        $operatorField->setValue($operator);

        if ('' !== $value) {
            list($data, $unit) = explode(' ', $value);

            $field = $this->spin(function () {
                return $this->element->find('css', '[name="filter-data"]');
            }, 'Cannot find the metric data field');

            $field->setValue($data);
            $this->element->getSession()->executeScript(
                sprintf(
                    '$(\'.filter-item[data-name="%s"][data-type="%s"] [name="filter-data"]\').trigger(\'change\')',
                    $this->element->getAttribute('data-name'),
                    $this->element->getAttribute('data-type')
                )
            );

            $unitField = $this->spin(function () {
                return $this->element->find('css', '.select2-container.unit');
            }, 'Cannot find the metric unit field');

            $unitField = $this->decorate(
                $unitField,
                [Select2Decorator::class]
            );

            $unitField->setValue($unit);
            $this->element->getSession()->executeScript(
                sprintf(
                    '$(\'.filter-item[data-name="%s"][data-type="%s"] '.
                    'select[name="filter-unit"]\').trigger(\'change\')',
                    $this->element->getAttribute('data-name'),
                    $this->element->getAttribute('data-type')
                )
            );
        }
    }
}
