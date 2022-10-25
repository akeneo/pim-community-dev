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
        $this->element->getSession()->executeScript(sprintf("$('#%s').val('%s');", $this->element->getAttribute('id'), $value));
    }
}
