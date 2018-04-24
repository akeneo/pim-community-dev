<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Context\Traits\ClosestTrait;
use Pim\Behat\Decorator\ElementDecorator;

class OperatorDecorator extends ElementDecorator
{
    use ClosestTrait;

    use SpinCapableTrait;

    /**
     * Set the decorator value
     *
     * @param $value
     *
     * @throws TimeoutException
     */
    public function setValue($value)
    {
        // We can't use contains("%s") here, as ">=" contains ">" too, the css selector is not strict enough,
        // we need to do a perfect match on the label
        $this->spin(function () use ($value) {
            $this->click();
            $operatorChoices = $this->getClosest($this, 'AknDropdown')->findAll(
                'css',
                '.label, .AknDropdown-menu .choice_value, .AknDropdown-menu .operator_choice'
            );

            foreach ($operatorChoices as $choice) {
                if (strtolower($value) === strtolower(trim($choice->getText()))) {
                    $choice->click();

                    return true;
                }
            }

            return false;
        }, sprintf('Cannot select the operator "%s"', $value));
    }
}
