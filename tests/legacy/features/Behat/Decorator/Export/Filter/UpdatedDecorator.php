<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

class UpdatedDecorator extends ElementDecorator
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
            $this->element->find('css', '.select2-container.operator'),
            [Select2Decorator::class]
        );
        $operatorField->setValue($operator);

        if ('' !== $value) {
            $fieldSelector = sprintf(
                '.filter-item[data-name="%s"][data-type="%s"] [name="filter-value-updated"]',
                $this->element->getAttribute('data-name'),
                $this->element->getAttribute('data-type')
            );
            $valueField = $this->element->find('css', $fieldSelector);

            $valueField->setValue($value);

            $this->element->getSession()->executeScript(
                sprintf(
                    '$(\'%s\').trigger(\'change\')',
                    $fieldSelector
                )
            );
        }
    }
}
