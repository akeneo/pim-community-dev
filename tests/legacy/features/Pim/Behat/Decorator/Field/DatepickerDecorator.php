<?php

namespace Pim\Behat\Decorator\Field;

use Pim\Behat\Decorator\ElementDecorator;

class DatepickerDecorator extends ElementDecorator
{
    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->getSession()->executeScript(sprintf("$('#%s').val('%s');", $this->getAttribute('id'), $value));
    }
}
