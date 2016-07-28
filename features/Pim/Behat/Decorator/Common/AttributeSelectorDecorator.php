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
            $this->find('css', 'header input')->setValue($attribute);

            $attributeItem = $this->spin(function () use ($attribute) {
                return $this->find('css', sprintf('li[data-attribute-code="%s"]', $attribute));
            }, sprintf('Cannot find the attribute %s in the list', $attribute));


            $dropZone = $this->spin(function () {
                return $this->find('css', '.selected-attributes ul');
            }, sprintf('Cannot find the drop zone', $attribute));

            $this->dragElementTo($attributeItem, $dropZone);
        }
    }

    /**
     * Close the modal
     */
    public function close()
    {
        $this->find('css', '.btn.ok')->click();
    }

    /**
     * Clear the selected attributes
     */
    public function clear()
    {
        $this->find('css', '.btn.clear')->click();
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
