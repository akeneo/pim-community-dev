<?php

namespace Pim\Behat\Decorator\Navbar;

use Behat\Mink\Element\Element;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ButtonDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Button' => ['css' => 'button:contains("%s")'],
    ];

    /**
     * @param string $label
     */
    public function clickButton($label)
    {
        $button = $this->getButton($label);
        $button->press();
    }

    /**
     * @param string $label
     *
     * @throws TimeoutException
     */
    public function asynchronousSave($label)
    {
        $button = $this->getButton($label);
        $button->press();

        $this->spin(function () {
            return null === $this->getSession()->getPage()->find(
                'css',
                '*:not(.hash-loading-mask):not(.grid-container):not(.loading-mask) > .loading-mask'
            );
        });
    }

    /**
     * @param string $label
     *
     * @return Element
     *
     * @throws TimeoutException
     */
    protected function getButton($label)
    {
        $selector = sprintf($this->selectors['Button']['css'], $label);

        $button = $this->spin(function () use ($label, $selector) {
            return $this->find('css', $selector);
        }, sprintf('Cannot find the button with label %s (%s)', $label, $selector));

        return $button;
    }
}
