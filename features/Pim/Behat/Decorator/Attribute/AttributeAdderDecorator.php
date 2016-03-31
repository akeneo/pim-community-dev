<?php

namespace Pim\Behat\Decorator\Attribute;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeAdderDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Available attributes button'     => ['css' => '.add-attribute a.select2-choice'],
        'Available attributes list'       => ['css' => '.add-attribute .select2-results'],
        'Available attributes search'     => ['css' => '.add-attribute .select2-search input[type="text"]'],
        'Available attributes add button' => ['css' => '.add-attribute .ui-multiselect-footer button'],
        'Select2 dropmask'                => ['css' => '.select2-drop-mask']
    ];

    /**
     * @param string $attribute
     * @param string $group
     *
     * @return NodeElement|null
     */
    public function findAvailableAttributeInGroup($attribute, $group)
    {
        $searchSelector = $this->selectors['Available attributes search']['css'];

        $selector = $this->spin(function () {
            return $this->find('css', $this->selectors['Available attributes button']['css']);
        }, sprintf('Cannot find element "%s"', $this->selectors['Available attributes button']['css']));

        // Open select2
        $selector->click();

        $list = $this->spin(function () {
            return $this->find('css', $this->selectors['Available attributes list']['css']);
        }, 'Cannot find the attribute list element');

        // We NEED to fill the search field with jQuery to avoid the TAB key press (because of mink),
        // because select2 selects the first element on TAB key press.
        $this->getSession()->evaluateScript(
            "jQuery('" . $searchSelector . "').val('" . $attribute . "').trigger('input');"
        );

        $groupLabels = $this->spin(function () use ($list, $group) {
            return $list->findAll('css', sprintf('li .group-label:contains("%s"), li.select2-no-results', $group));
        }, 'Cannot find element in the attribute list');

        // Maybe a "No matches found"
        $firstResult = $groupLabels[0];
        $text = $firstResult->getText();
        $results = [];

        if ('No matches found' !== $text) {
            foreach ($groupLabels as $groupLabel) {
                $li = $groupLabel->getParent();
                $results[$li->find('css', '.attribute-label')->getText()] = $li;
            }
        }

        // Close select2
        $this->getSession()->evaluateScript(
            "jQuery('" . $this->selectors['Select2 dropmask']['css'] . "').click();"
        );

        return isset($results[$attribute]) ? $results[$attribute] : null;
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Used with the new 'add-attributes' module. The method should be in the Form parent
     * when legacy stuff is removed.
     */
    public function addAvailableAttributes(array $attributes = [])
    {
        $searchSelector = $this->selectors['Available attributes search']['css'];

        $selector = $this->spin(function () {
            return $this->find('css', $this->selectors['Available attributes button']['css']);
        }, sprintf('Cannot find element "%s"', $this->selectors['Available attributes button']['css']));

        // Open select2
        $selector->click();

        $list = $this->spin(function () {
            return $this->find('css', $this->selectors['Available attributes list']['css']);
        }, sprintf('Cannot find the attribute list element'));

        foreach ($attributes as $attributeLabel) {
            // We NEED to fill the search field with jQuery to avoid the TAB key press (because of mink),
            // because select2 selects the first element on TAB key press.
            $this->getSession()->evaluateScript(
                sprintf("jQuery('%s').val('%s').trigger('input');", $searchSelector, $attributeLabel)
            );

            $selector = sprintf('li .attribute-label:contains("%s")', $attributeLabel);

            $label = $this->spin(
                function () use ($list, $attributeLabel, $selector) {
                    return $list->find('css', $selector);
                },
                sprintf('Could not find available attribute "%s" (%s)', $attributeLabel, $selector)
            );

            $label->click();
        }

        $this->find('css', $this->selectors['Available attributes add button']['css'])->press();
    }
}
