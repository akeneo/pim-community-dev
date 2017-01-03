<?php

namespace Pim\Behat\Decorator\Common;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorate the add attribute element
 */
class AddAttributeDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Add the given attributes
     *
     * @param array $attributes
     */
    public function addAttributes(array $attributes)
    {
        $selectorButton = $this->spin(function () {
            return $this->find('css', 'a.select2-choice');
        }, sprintf('Cannot find element "%s"', '.ui-multiselect-footer button'));

        // Open select2
        $selectorButton->click();

        $list = $this->spin(function () {
            return $this->find('css', '.select2-results');
        }, 'Cannot find the attribute list element');

        foreach ($attributes as $attributeLabel) {
            // We NEED to fill the search field with jQuery to avoid the TAB key press (because of mink),
            // because select2 selects the first element on TAB key press.
            $this->getSession()->evaluateScript(
                sprintf(
                    'jQuery(\'.%s %s\').val(\'%s\').trigger(\'input\');',
                    implode('.', explode(' ', $this->getAttribute('class'))),
                    '.select2-search input[type="text"]',
                    $attributeLabel
                )
            );
            $label = $this->spin(
                function () use ($list, $attributeLabel) {
                    return $list->find('css', sprintf('li .attribute-label:contains("%s")', $attributeLabel));
                },
                sprintf('Could not find available attribute "%s".', $attributeLabel)
            );

            $label->click();
        }

        $this->find('css', '.ui-multiselect-footer button')->press();

        // Clean extra select2-drop in the DOM
        $this->getSession()->evaluateScript('jQuery(\'.select2-drop:hidden\').remove();');
    }
}
