<?php

namespace Pim\Behat\Decorator\Attribute;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $value
     */
    public function fill($value)
    {
        $amount   = null;
        $currency = null;

        if (false !== strpos($value, ' ')) {
            list($amount, $currency) = explode(' ', $value);
        }

        if (null === $currency) {
            throw new \InvalidArgumentException('Field is compound but the sub label was not provided');
        }

        $currencyInput = $this->getInput($currency);
        $currencyInput->setValue($amount);

        $this->getSession()->executeScript(
            '$(\'.field-input input[type="text"]\').trigger(\'change\');'
        );
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        // TODO with currency ?
    }

    /**
     * @return NodeElement
     */
    protected function getInput($currency)
    {
        $input = $this->spin(function () use ($currency) {
            return $this->find(sprintf('input[data-currency=%s]', $currency));
        }, 'Cannot find input for currency "%s"', $currency);

        return $input;
    }
}
