<?php

namespace Pim\Behat\Decorator\Tab;

use Behat\Mink\Element\Element;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to add comparison feature to an element
 */
class ComparableTabDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Start copy button' => '.start-copying',
        'Stop copy button'  => '.attribute-copy-actions .stop-copying'
    ];

    /**
     * Enter in copy mode
     */
    public function startComparison()
    {
        $this->spin(function () {
            $dropdown = $this->getBody()->find('css', '.secondary-actions');
            if (null === $dropdown) {
                return false;
            }
            if (!$dropdown->hasClass('open')) {
                $dropdown->click();
            }

            $start = $dropdown->find('css', $this->selectors['Start copy button']);
            if (null === $start) {
                return false;
            }
            $start->click();

            return true;
        }, 'Can not click on start copy button');
    }

    /**
     * Manually select translation given the specified field label
     *
     * @param string $fieldLabel
     */
    public function manualSelectComparedElement($fieldLabel)
    {
        $fieldContainer = $this->spin(function () use ($fieldLabel) {
            return $this->getComparisonFieldContainer($fieldLabel);
        }, sprintf('Cannot find the %s field label', $fieldLabel));

        $this->spin(function () use ($fieldContainer) {
            return $fieldContainer->find('css', '.copy-field-selector');
        }, 'Cannot find the check selector')->check();
    }

    /**
     * @param string $field
     *
     * @return Element
     */
    public function getLabelField(string $field)
    {
        return $this->spin(function () use ($field) {
            return $this->find('css', sprintf('.copy-container .AknFieldContainer-label:contains("%s")', $field));
        }, sprintf('Cannot find the comparison field with the label "%s"', $field));
    }

    /**
     * Get a comparison field container
     *
     * @param string $fieldLabel
     */
    public function getComparisonFieldContainer($fieldLabel)
    {
        $label = $this->getLabelField($fieldLabel);

        return $label->getParent()->getParent()->getParent();
    }
}
