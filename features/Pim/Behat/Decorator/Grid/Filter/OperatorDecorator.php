<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class OperatorDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Set the decorator value
     */
    public function setValue($value)
    {
        // We can't use contains("%s") here, as ">=" contains ">" too, the css selector is not strict enough,
        // we need to do a perfect match on the label
        $this->spin(function () use ($value) {
            $this->click();
            $operatorChoices = $this->getParent()->findAll('css', '.dropdown-menu .choice_value, .dropdown-menu .operator_choice');

            foreach ($operatorChoices as $choice) {
                if ($value === $choice->getText()) {
                    $choice->click();
                    return true;
                }
            }

            return false;
        }, sprintf('Cannot select the operator "%s"', $value));
    }
}
