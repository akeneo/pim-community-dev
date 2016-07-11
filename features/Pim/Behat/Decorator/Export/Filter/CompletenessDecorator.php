<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class CompletenessDecorator extends ElementDecorator
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
            $this->find('css', '.operator.select2-container'),
            ['Pim\Behat\Decorator\Field\Select2Decorator']
        );
        $operatorField->setValue($operator);
    }
}
