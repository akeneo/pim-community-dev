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
        $dropdown = $this->spin(function () use ($localeCode) {
            $dropdown = $this->find('css', $this->selectors['Locales dropdown']);
            if (null === $dropdown) {
                return false;
            }

            $toggle = $dropdown->find('css', '.dropdown-toggle');

            if (null === $toggle) {
                return false;
            }
            $toggle->click();

            $option = $dropdown->find('css', sprintf('a[data-locale="%s"], a[href*="%s"]', $localeCode, $localeCode));
            if (null === $option) {
                return false;
            }
            $option->click();

            return true;
        }, 'Could not find locale switcher');
    }

    /**
     * @param string $localeCode
     *
     * @return bool
     */
    public function hasSelectedLocale($localeCode)
    {
        $dropdown = $this->spin(function () {
            return $this->find('css', $this->selectors['Locales dropdown']);
        }, 'Could not find locale switcher');

        $selectedLocale = $this->spin(function () use ($dropdown, $localeCode) {
            return $dropdown->find('css', sprintf('li.active a[href*="%s"]', $localeCode));
        }, sprintf(
            'Locale is expected to be "%s", actually is "%s".',
            $localeCode,
            $dropdown->find('css', sprintf('li.active a'))->getAttribute('title')
        ));

        return true;
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
                return false;
            }
            $toggle->click();

            $option = $dropdown->find('css', sprintf('a[data-scope="%s"], a[href*="%s"]', $scopeCode, $scopeCode));
            if (null === $option) {
                return false;
            }
            $option->click();

            return true;
        }, 'Could not find scope switcher');
    }
}
