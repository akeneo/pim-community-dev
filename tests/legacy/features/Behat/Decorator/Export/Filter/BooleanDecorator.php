<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * This decorator allows to manipulate Bootstrap switch filters.
 */
class BooleanDecorator extends ElementDecorator
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
        $wrapper = $this->spin(function () {
            return $this->find('css', '.switch-animate');
        }, 'Can\'t find Bootstrap switch wrapper');

        $value = 'Yes' === $value;
        if ($wrapper->hasClass('switch-on') !== $value) {
            $wrapper->find('css', 'span')->click();
        }
    }
}
