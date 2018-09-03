<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

class DateDecorator extends ElementDecorator
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
            $operatorField = $this->decorate(
                $this->find('css', '.operator.select2-container'),
                [Select2Decorator::class]
            );
            $operatorField->setValue($operator);
        }

        if (null !== $value && '' !== $value) {
            if (false !== strstr($value, 'and')) {
                $dates = explode(' and ', $value);
            } elseif ('' !== $value) {
                $dates = [$value];
            } else {
                $dates = [];
            }

            switch ($operator) {
                case 'Between':
                case 'Not between':
                    $this->setValueToDatepicker('value-start', $dates[0]);
                    $this->setValueToDatepicker('value-end', $dates[1]);
                    break;
                case 'Empty':
                case 'Not empty':
                    break;
                default:
                    $this->setValueToDatepicker('value-start', $dates[0]);
                    break;
            }
        }
    }

    /**
     * @param string $element 'value-start', 'value-end'
     * @param string $value
     *
     * @return NodeElement
     */
    protected function setValueToDatepicker($element, $value)
    {
        $this->spin(function () use ($element, $value) {
            $field = $this->find(
                'css',
                sprintf(
                    '.filter-item[data-name="%s"][data-type="%s"] [name="filter-%s"]',
                    $this->getAttribute('data-name'),
                    $this->getAttribute('data-type'),
                    $element
                )
            );
            $field->setValue($value);
            $this->triggerChange($element);

            return true;
        }, sprintf('Cannot find or set value to %s date filter.', $element));
    }

    /**
     * @param array $element 'value-end', 'value-start', 'operator'
     */
    protected function triggerChange($element)
    {
        $this->getSession()->executeScript(
            sprintf(
                '$(\'.filter-item[data-name="%s"][data-type="%s"] [name="filter-%s"]\').trigger(\'change\')',
                $this->getAttribute('data-name'),
                $this->getAttribute('data-type'),
                $element
            )
        );
    }
}
