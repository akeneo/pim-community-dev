<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Pim\Behat\Decorator\ElementDecorator;

class SearchDecorator extends ElementDecorator
{
    /**
     * Search a value in the search filter
     *
     * @param string $value
     */
    public function search($value) {
        $this->setValue($value);
    }
}
