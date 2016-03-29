<?php

namespace Pim\Behat\Decorator\NodeDecorator\TabDecorator;

use Behat\Mink\Exception\ElementNotFoundException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to add comparison feature to an element
 */
class ComparableTabDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Start copy button' => '.attribute-copy-actions .start-copying',
        'Stop copy button'  => '.attribute-copy-actions .stop-copying'
    ];

    /**
     * Enter in copy mode
     */
    public function startComparison()
    {
        $startCopyBtn = $this->spin(function () {
            return $this->find('css', $this->selectors['Start copy button']);
        }, 'Cannot find the start copy button');
        $startCopyBtn->click();
    }

    /**
     * Manually select translation given the specified field label
     *
     * @param string $fieldLabel
     */
    public function manualSelectComparedElement($fieldLabel)
    {
        $fieldContainer = $this->getComparisonFieldContainer($fieldLabel);

        $this->spin(function () use ($fieldContainer) {
            return $fieldContainer->find('css', '.copy-field-selector');
        }, 'Cannot find the check selector')->check();
    }

    /**
     * Get a comparison field container
     *
     * @param string $fieldLabel
     */
    public function getComparisonFieldContainer($fieldLabel)
    {
        $label = $this->spin(function () use ($fieldLabel) {
            return $this->find('css', sprintf('.copy-container header label:contains("%s")', $fieldLabel));
        }, sprintf('Cannot find the comparison field with the label "%s"', $fieldLabel));

        return $label->getParent()->getParent()->getParent();
    }
}
