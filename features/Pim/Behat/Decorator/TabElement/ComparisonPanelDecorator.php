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
        $this->spin(function () use ($mode) {
            $dropdown = $this->find('css', $this->selectors['Change selection dropdown']);
            if (null === $dropdown) {
                return false;
            }
            $dropdown->click();

            $selector = $dropdown->getParent()->find('css', sprintf('a:contains("%s")', ucfirst($mode)));
            if (null === $selector) {
                return false;
            }
            $selector->click();

            return true;
        }, sprintf('Can not select "%s" elements', $mode));
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
            $copyButton = $this->find('css', $this->selectors['Copy selected button']);
            if (null === $copyButton) {
                return false;
            }
            $copyButton->click();

            return 0 === $this->selectedItemsCount();
        }, 'Still a selection after copy');
    }

    /**
     * @param string $source
     */
    public function switchSource($source)
    {
        $this->spin(function () use ($source) {
            $dropdown = $this->find('css', $this->selectors['Copy source dropdown']);
            if (null === $dropdown) {
                return false;
            }

            $toggle = $dropdown->find('css', '.AknActionButton');
            if (null === $toggle) {
                return false;
            }
            $toggle->click();

            $option = $dropdown->find('css', sprintf('.AknDropdown-menuLink[data-source="%s"]', $source));
            if (null === $option) {
                return false;
            }
            $option->click();

            return true;
        }, 'Can not switch source ');
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
