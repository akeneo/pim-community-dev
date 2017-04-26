<?php

namespace Pim\Behat\Decorator\Page;

use Behat\Mink\Exception\ElementNotFoundException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to add panel management feature to an element
 *
 * TODO to remove
 */
class PanelableDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Panel selector'  => '.panel-selector',
        'Panel container' => '.panel-container',
    ];

    /**
     * Open the specified panel
     *
     * @param string $panel
     */
    public function openPanel($panel)
    {
        $this->spin(function () use ($panel) {
            $elt = $this->find('css', $this->selectors['Panel selector']);
            if (null === $elt) {
                return false;
            }

            $panel = strtolower($panel);
            if (null === $elt->find('css', sprintf('button[data-panel$="%s"].active', $panel))) {
                $button =  $elt->find('css', sprintf('button[data-panel$="%s"]', $panel));
                if (null === $button) {
                    return null;
                }

                $button->click();
            }

            return $elt->find('css', sprintf('button[data-panel$="%s"].active', $panel));
        }, sprintf('Cannot open the %s panel', $panel));
    }

    /**
     * Close the specified panel
     *
     * @throws \Context\Spin\TimeoutException
     */
    public function closePanel()
    {
        $elt = $this->spin(function () {
            return $this->find('css', $this->selectors['Panel container'] . ' header .close');
        }, 'Cannot find the panel close button');

        $elt->click();
    }
}
