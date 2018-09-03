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

    /**
     * @param string $localeCode
     */
    public function switchLocale($localeCode)
    {
        $this->spin(function () use ($localeCode) {
            $dropdown = $this->find('css', $this->selectors['Locales dropdown']);
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
                sprintf('*[data-locale="%s"], a[href*="%s"]', $localeCode, $localeCode)
            );
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

            $toggle = $dropdown->find('css', '.dropdown-toggle, *[data-toggle="dropdown"]');

            if (null === $toggle) {
                return false;
            }
            $toggle->click();

            $option = $dropdown->find('css', sprintf(
                'a[data-scope="%s"], a[href*="%s"], *[data-value="%s"]',
                $scopeCode,
                $scopeCode,
                $scopeCode
            ));
            if (null === $option) {
                return false;
            }
            $option->click();

            return true;
        }, 'Could not find scope switcher');
    }
}
