<?php

namespace Pim\Behat\Decorator\Navbar;

use Behat\Mink\Element\Element;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator for NavBar buttons, ie: Save, Delete, Publish...
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ButtonDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Button' => 'button:contains("%s")',
    ];

    /**
     * Click the button in the NavBar with the given $label
     *
     * @param string $label
     */
    public function clickButton($label)
    {
        $button = $this->getButton($label);

        $this->spin(function () use ($button) {
            return $button->isVisible();
        }, 'Waiting for save button to be visible');

        $button->press();
    }

    /**
     * Get the button in the NavBar with the given $label
     *
     * @param string $label
     *
     * @return Element
     *
     * @throws TimeoutException
     */
    protected function getButton($label)
    {
        $selector = sprintf($this->selectors['Button'], $label);

        $button = $this->spin(function () use ($label, $selector) {
            return $this->find('css', $selector);
        }, sprintf('Cannot find the button with label %s', $label));

        return $button;
    }
}
