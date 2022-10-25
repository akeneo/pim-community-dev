<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * Decorator dedicated to metric attribute type.
 */
class MediaDecorator extends ElementDecorator
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
        }, 'Cannot find the media operator field');

        $operatorField = $this->decorate(
            $operatorField,
            [Select2Decorator::class]
        );
        $operatorField->setValue($operator);

        if ('' !== $value) {
            $field = $this->spin(function () {
                return $this->element->find('css', '[name="filter-value"]');
            }, 'Cannot find the media data field');

            $field->setValue($value);
            $this->element->getSession()->executeScript(
                sprintf(
                    '$(\'.filter-item[data-name="%s"][data-type="%s"] [name="filter-value"]\').trigger(\'change\')',
                    $this->element->getAttribute('data-name'),
                    $this->element->getAttribute('data-type')
                )
            );
        }
    }
}
