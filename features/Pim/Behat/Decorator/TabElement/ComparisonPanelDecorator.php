<?php

namespace Pim\Behat\Decorator\TabElement;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to add comparison feature to an element
 */
class ComparisonPanelDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Change selection dropdown' => '.attribute-copy-actions .selection-dropdown .dropdown-toggle',
        'Copy selected button'      => '.attribute-copy-actions .copy',
        'Copy source dropdown'      => '.attribute-copy-actions .source-switcher',
    ];

    /**
     * Change the current comparison selection given the specified mode ("all visible" or "all")
     *
     * @param string $mode
     */
    public function selectElements($mode)
    {
        $dropdown = $this->spin(function () {
            return $this->find('css', $this->selectors['Change selection dropdown']);
        }, 'Cannot find the select element dropdown');

        $dropdown->click();

        $selector = $dropdown->getParent()->find('css', sprintf('a:contains("%s")', ucfirst($mode)));
        $selector->click();
    }

    /**
     * Click the link to copy selected translations
     */
    public function copySelectedElements()
    {
        $this->spin(function () {
            return $this->find('css', $this->selectors['Copy selected button']);
        }, 'Cannot find the "copy" button')->click();
    }

    /**
     * @param string $source
     */
    public function switchSource($source)
    {
        $dropdown = $this->spin(function () {
            return $this->find('css', $this->selectors['Copy source dropdown']);
        }, 'Cannot find the comparison source dropdown');

        $toggle = $this->spin(function () use ($dropdown) {
            return $dropdown->find('css', '.dropdown-toggle');
        }, 'Could not find copy source switcher.');
        $toggle->click();

        $option = $this->spin(function () use ($dropdown, $source) {
            return $dropdown->find('css', sprintf('a[data-source="%s"]', $source));
        }, sprintf('Could not find source "%s" in switcher', $source));
        $option->click();
    }
}
