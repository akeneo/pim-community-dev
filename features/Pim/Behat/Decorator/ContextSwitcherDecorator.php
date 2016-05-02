<?php

namespace Pim\Behat\Decorator;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

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
        $dropdown = $this->spin(function () {
            return $this->find('css', $this->selectors['Locales dropdown']);
        }, 'Could not find locale switcher');

        $toggle = $this->spin(function () use ($dropdown) {
            return $dropdown->find('css', '.dropdown-toggle');
        }, 'Cannot find ".dropdown-toggle" element in locale switcher');
        $toggle->click();

        $option = $this->spin(function () use ($dropdown, $localeCode) {
            return $dropdown->find('css', sprintf('a[data-locale="%s"], a[href*="%s"]', $localeCode, $localeCode));
        }, sprintf('Could not find locale "%s" in switcher', $localeCode));
        $option->click();
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
     * @throws \Exception
     */
    public function switchScope($scopeCode)
    {
        $dropdown = $this->spin(function () {
            return $this->find('css', $this->selectors['Channel dropdown']);
        }, 'Could not find scope switcher');

        $toggle = $this->spin(function () use ($dropdown) {
            return $dropdown->find('css', '.dropdown-toggle');
        }, 'Cannot find ".dropdown-toggle" element in scope switcher');
        $toggle->click();

        $option = $this->spin(function () use ($dropdown, $scopeCode) {
            return $dropdown->find('css', sprintf('a[data-scope="%s"]', $scopeCode));
        }, sprintf('Could not find scope "%s" in switcher', $scopeCode));
        $option->click();
    }
}
