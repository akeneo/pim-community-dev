<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

class StringDecorator extends ElementDecorator
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
        }, 'Cannot find the operator field');

        $operatorField = $this->decorate(
            $operatorField,
            [Select2Decorator::class]
        );
        $operatorField->setValue($operator);

        if ('' !== $value) {
            list($data) = explode(' ', $value);

            $field = $this->element->find('css', '[name="filter-value"]');
            $field->setValue($data);
            $this->element->getSession()->executeScript(
                sprintf(
                    '$(\'.filter-item[data-name="%s"][data-type="%s"] [name="filter-value"]\').trigger(\'change\')',
                    $this->element->getAttribute('data-name'),
                    $this->element->getAttribute('data-type')
                )
            );
        }
    }

    /**
     * Return whether this filter input value is visible
     *
     * @return bool
     */
    public function isInputValueVisible()
    {
        try {
            $filterInput = $this->spin(function () {
                return $this->element->find('css', '[name="filter-value"]');
            }, 'Cannot find the filter-value input');
        } catch (TimeoutException $exception) {
            return false;
        }

        return $filterInput && $filterInput->isVisible();
    }
}
