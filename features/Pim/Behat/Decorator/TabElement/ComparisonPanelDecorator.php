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
        'Change selection dropdown' => '.attribute-copy-actions .selection-dropdown *[data-toggle="dropdown"]',
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

        $selector = $this->spin(function () use ($mode, $dropdown) {
            return $dropdown->getParent()->find('css', sprintf('a.select-%s', str_replace(' ', '-', $mode)));
        }, sprintf('Unable to find the mode %s', $mode));
        $selector->click();
    }

    /**
     * Click the link to copy selected translations
     */
    public function copySelectedElements()
    {
        $this->spin(function () {
            return 0 !== $this->selectedItemsCount();
        }, 'No selection before copy');

        $this->spin(function () {
            return $this->find('css', $this->selectors['Copy selected button']);
        }, 'Cannot find the "copy" button')->click();

        $this->spin(function () {
            return 0 === $this->selectedItemsCount();
        }, 'Still a selection after copy');
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
            return $dropdown->find('css', '.AknActionButton');
        }, 'Could not find copy source switcher.');
        $toggle->click();

        $option = $this->spin(function () use ($dropdown, $source) {
            return $dropdown->find('css', sprintf('.AknDropdown-menuLink[data-source="%s"]', $source));
        }, sprintf('Could not find source "%s" in switcher', $source));
        $option->click();
    }

    /**
     * Get le count of selected items in the panel
     *
     * @return integer
     */
    protected function selectedItemsCount()
    {
        $checkboxes = $this->getBody()->findAll('css', '.copy-field-selector');
        $checkedCount = 0;
        foreach ($checkboxes as $checkbox) {
            if ($checkbox->isChecked()) {
                $checkedCount++;
            }
        }

        return $checkedCount;
    }
}
