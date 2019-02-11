<?php

namespace Pim\Behat\Decorator\Common;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorate the add attribute element
 */
class AttributeSelectorDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Select the given attributes
     *
     * @param array $attributes
     */
    public function selectAttributes(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $this->spin(function () use ($attribute) {
                $headerInput = $this->find('css', 'header input');

                if (!$headerInput->isVisible() && $headerInput->isValid()) {
                    return false;
                }

                $headerInput->setValue($attribute);

                return true;
            }, 'Cannot fill the header input');

            $attributeItem = $this->spin(function () use ($attribute) {
                return $this->find('css', sprintf('li[data-attribute-code="%s"]', $attribute));
            }, sprintf('Cannot find the attribute %s in the list', $attribute));

            $dropZone = $this->spin(function () {
                return $this->find('css', '.selected-attributes ul');
            }, 'Cannot find the drop zone to select attributes');

            $this->dragElementTo($attributeItem, $dropZone);
        }
    }

    /**
     * Close the modal
     */
    public function close()
    {
        $button = $this->spin(function () {
            return $this->find('css', '.modal .ok');
        }, 'Cannot find the close button');

        $button->click();
    }

    /**
     * Clear the selected attributes
     */
    public function clear()
    {
        $button = $this->spin(function () {
            return $this->find('css', '.reset, .clear');
        }, 'Cannot find the clear button');

        $button->click();
        $this->spin(function () {
            $selectedAttributes = $this->find(
                'css',
                '.selected-attributes .AknColumnConfigurator-listContainer .AknVerticalList'
            );
            return false === $selectedAttributes->has('css', '.AknVerticalList-item');
        }, 'Cannot clear the selected attributes');
    }

    /**
     * Drags an element on another one.
     * Works better than the standard dragTo.
     *
     * @param $element
     * @param $dropZone
     */
    protected function dragElementTo($element, $dropZone)
    {
        $session = $this->getSession()->getDriver()->getWebDriverSession();

        $from = $session->element('xpath', $element->getXpath());
        $to = $session->element('xpath', $dropZone->getXpath());

        $session->moveto(['element' => $from->getID()]);
        $session->buttondown('');
        $session->moveto(['element' => $to->getID()]);
        $session->buttonup('');
    }
}
