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
    }

    /**
     * Set the filter locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $localeSwitcher = $this->spin(function () {
            return $this->find('css', '.locale-switcher');
        }, 'Cannot find the locale switcher. Are you sure that this attribute is localizable?');

        $localeSwitcher->find('css', '.dropdown-toggle, *[data-toggle="dropdown"]')->click();
        $localeSwitcher->find('css', sprintf('a[data-locale="%s"]', $locale))->click();
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
