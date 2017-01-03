<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

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
            $this->find('css', '.select2-container.operator'),
            ['Pim\Behat\Decorator\Field\Select2Decorator']
        );
        $operatorField->setValue($operator);

        if ('' !== $value) {
            $valueField = $this->find(
                'css',
                sprintf(
                    '.filter-item[data-name="%s"][data-type="%s"] [name="filter-value-updated"]',
                    $this->getAttribute('data-name'),
                    $this->getAttribute('data-type')
                )
            );

            $valueField->setValue($value);
        }
    }
}
