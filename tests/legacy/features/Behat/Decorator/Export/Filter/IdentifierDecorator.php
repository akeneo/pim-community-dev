<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class IdentifierDecorator extends ElementDecorator
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
        $field = $this->spin(function () {
            return $this->find('css', '.identifier');
        }, 'Cannot find identifier element.');
        $field->setValue($value);
        $this->getSession()->executeScript('$(\'.identifier\').trigger(\'change\')');
    }
}
