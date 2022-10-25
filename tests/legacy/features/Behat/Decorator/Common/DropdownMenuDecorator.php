<?php

namespace Pim\Behat\Decorator\Common;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorates dropdown element with links inside
 */
class DropdownMenuDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Opens the Dropdown by clicking on the button
     */
    public function open()
    {
        $this->spin(function () {
            if ($this->element->hasClass('open')) {
                return true;
            }

            $button = $this->element->find('css', '.dropdown-button');
            if (null !== $button) {
                $button->click();

                return true;
            }

            if ('button' === $this->element->getTagName()) {
                if (null === $this->element->getParent()->find('css', 'div[data-testid="backdrop"]')) {
                    $this->element->click();
                }

                return true;
            }

            return false;
        }, 'Could not find open DropdownMenu button');
    }

    /**
     * Returns the DOM element item of the dropdown menu from its name.
     * If no item is found, returns null.
     *
     * @param $actionName
     *
     * @return NodeElement|null
     */
    public function getMenuItem($actionName)
    {
        $dropdownMenu = $this->getBody()->findById('dropdown-root') ?? $this->element->getParent();

        $links = $dropdownMenu->findAll('css', '.AknDropdown-menuLink, a');
        foreach ($links as $link) {
            if (trim($link->getText()) === $actionName && $link->isVisible()) {
                return $link;
            }
        }

        return null;
    }

    /**
     * Closes the dropdown menu. It clicks out of the dropdown to close.
     */
    public function close()
    {
        if ($this->getBody()->find('css', 'div[data-testid="backdrop"]') || $this->element->hasClass('open')) {
            $this->getBody()->click();
        }
    }
}
