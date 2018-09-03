<?php

namespace Pim\Behat\Decorator\Grid\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

class DateDecorator extends ElementDecorator
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
        $operatorDropdown = $this->decorate(
            $this->find('css', '*[data-toggle="dropdown"]'),
            [OperatorDecorator::class]
        );
        $operatorDropdown->setValue($operator);

        if (false !== strstr($value, 'and')) {
            $dates = explode(' and ', $value);
        } elseif ('' !== $value) {
            $dates = [$value];
        } else {
            $dates = [];
        }

        // Depending on the operator, we won't fill the same inputs
        $inputStart = $this->find('css', 'input[name="start"]');
        $inputEnd   = $this->find('css', 'input[name="end"]');

        switch ($operator) {
            case 'between':
            case 'not between':
                $inputStart->setValue($dates[0]);
                $inputEnd->setValue($dates[1]);
                break;
            case 'more than':
                $inputStart->setValue($dates[0]);
                break;
            case 'less than':
                $inputEnd->setValue($dates[0]);
                break;
            default:
                if ($inputStart->isVisible()) {
                    $inputStart->setValue('');
                }
                if ($inputEnd->isVisible()) {
                    $inputEnd->setValue('');
                }
                break;
        }

        // We submit the filter
        $this->spin(function () {
            if (!$this->find('css', '.filter-criteria')->isVisible()) {
                return true;
            }
            $this->find('css', '.filter-update')->click();

            return false;
        }, 'Cannot update the filter');
    }
}
