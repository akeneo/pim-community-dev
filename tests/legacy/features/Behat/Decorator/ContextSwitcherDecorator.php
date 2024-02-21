<?php

namespace Pim\Behat\Decorator;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;

/**
 * Decorator to add switch context feature to an element
 */
class ContextSwitcherDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Locales dropdown' => '.locale-switcher',
        'Channel dropdown' => '.scope-switcher',
    ];

    public function switchLocale(string $localeCode): void
    {
        $this->spin(function () use ($localeCode) {
            $dropdown = $this->find('css', $this->selectors['Locales dropdown']);
            if (null === $dropdown) {
                return false;
            }

            $toggle = $dropdown->find('css', '.dropdown-toggle');
            if (null === $toggle) {
                $toggle = $dropdown->find('css', '*[data-toggle="dropdown"]');
            }

            if (null === $toggle) {
                return false;
            }
            $toggle->click();

            $option = $dropdown->find('css', sprintf('*[data-locale="%s"]', $localeCode));

            if (null === $option) {
                $option = $dropdown->find('css', sprintf('a[href*="%s"]', $localeCode));
            }
            if (null === $option) {
                return false;
            }
            $option->click();

            return $this->getSelectedLocale() === $localeCode;
        }, sprintf('Could not switch locale to "%s"', $localeCode));
    }

    /**
     * @return string
     */
    public function getSelectedLocale()
    {
        $dropdown = $this->spin(function () {
            return $this->find('css', $this->selectors['Locales dropdown']);
        }, 'Could not find locale switcher');

        $active = $this->spin(function () use ($dropdown) {
            return $dropdown->find('css', '.AknDropdown-menuLink--active');
        }, 'Cannot find active locale');

        return $active->getAttribute('data-locale');
    }

    /**
     * @param string $scopeCode
     *
     * @throws TimeoutException
     */
    public function switchScope($scopeCode)
    {
        $this->spin(function () use ($scopeCode) {
            $dropdown = $this->find('css', $this->selectors['Channel dropdown']);
            if (null === $dropdown) {
                return false;
            }

            $toggle = $dropdown->find('css', '.dropdown-toggle');
            if (null === $toggle) {
                $toggle = $dropdown->find('css', '*[data-toggle="dropdown"]');
            }

            if (null === $toggle) {
                return false;
            }
            $toggle->click();

            $option = $dropdown->find('css', sprintf('a[data-scope="%s"]', $scopeCode));
            if (null === $option) {
                $option = $dropdown->find('css', sprintf('a[href*="%s"]', $scopeCode));
            }
            if (null === $option) {
                $option = $dropdown->find('css', sprintf('*[data-value="%s"]', $scopeCode));
            }

            if (null === $option) {
                return false;
            }
            $option->click();

            return true;
        }, 'Could not find scope switcher');
    }
}
