<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * This decorator is dedicated to export filters. It's a shortcut to avoit to rework the whole grid page.
 */
class BaseDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Opens the filter
     */
    public function open()
    {
    }

    /**
     * Remove the filter from the grid
     */
    public function remove()
    {
        $this->spin(function () {
            return $this->find('css', '.remove');
        }, 'Can not find the remove button.')->click();
    }

    /**
     * Set the filter locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $option = $this->spin(function () use ($locale) {
            $dropdown = $this->find('css', '.locale-switcher');

            if (null === $dropdown) {
                return false;
            }

            $toggle = $dropdown->find('css', '.dropdown-toggle, *[data-toggle="dropdown"]');
            if (null === $toggle) {
                return false;
            }
            $toggle->click();

            $option = $dropdown->find(
                'css',
                sprintf('*[data-locale="%s"], a[href*="%s"]', $locale, $locale)
            );

            return $option;
        }, 'Cannot find the locale switcher. Are you sure that this attribute is localizable?');

        $option->click();
    }

    /**
     * Set the filter scope
     *
     * @param string $scope
     */
    public function setScope($scope)
    {
        $scopeSwitcher = $this->spin(function () {
            return $this->find('css', '.scope-switcher');
        }, 'Cannot find the scope switcher. Are you sure that this attribute is scopable?');

        $scopeSwitcher->find('css', '.dropdown-toggle, *[data-toggle="dropdown"]')->click();
        $scopeSwitcher->find('css', sprintf('a[data-scope="%s"]', $scope))->click();
    }
}
