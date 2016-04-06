<?php

namespace Pim\Behat\Decorator\Attribute;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator for the Add Attributes element into forms
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeAdderDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Available attributes button'     => 'a.select2-choice',
        'Available attributes list'       => '.select2-results',
        'Available attributes search'     => '.select2-search input[type="text"]',
        'Available attributes add button' => '.ui-multiselect-footer button',
        'Select2 dropmask'                => '.select2-drop-mask',
    ];

    /**
     * In the attribute list, find the attribute with the given $label in the given attribute $group
     *
     * @param string $label
     * @param string $group
     *
     * @return NodeElement|null
     */
    public function findAvailableAttributeInGroup($label, $group)
    {
        $this->openSelector();
        $list = $this->findAttributeList();

        $this->searchAttribute($label);

        $groupLabels = $this->spin(function () use ($list, $group) {
            return $list->findAll('css', sprintf('li .group-label:contains("%s"), li.select2-no-results', $group));
        }, sprintf('Cannot find element "%s" in the attributes list', $label));

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
            sprintf("jQuery('%s').click();", $this->selectors['Select2 dropmask'])
        );

        return isset($results[$label]) ? $results[$label] : null;
    }

    /**
     * Add all the $attributes to the form
     *
     * @param array $attributes
     *
     * @throws TimeoutException
     */
    public function addAvailableAttributes(array $attributes = [])
    {
        $this->openSelector();
        $list = $this->findAttributeList();

        foreach ($attributes as $label) {
            $attributeCssSelector = sprintf('li .attribute-label:contains("%s")', $label);

            $this->searchAttribute($label);

            $label = $this->spin(
                function () use ($list, $label, $attributeCssSelector) {
                    return $list->find('css', $attributeCssSelector);
                },
                sprintf('Could not find available attribute "%s"', $label)
            );

            $label->click();
        }

        $this->find('css', $this->selectors['Available attributes add button'])->press();

        // Clean extra select2-drop in the DOM
        $this->getSession()->evaluateScript("jQuery('.select2-drop:hidden').remove();");
    }

    /**
     * Open Select2 to search for attributes
     *
     * @return Element
     *
     * @throws TimeoutException
     */
    protected function openSelector()
    {
        $selector = $this->spin(
            function () {
                return $this->find('css', $this->selectors['Available attributes button']);
            },
            sprintf('Cannot find the attribute selector "%s"', $this->selectors['Available attributes button'])
        );

        $selector->click();

        return $selector;
    }

    /**
     * Fill the search field with jQuery to avoid the TAB key press (because of mink),
     * because select2 selects the first element on TAB key press.
     *
     * @param string $label
     */
    protected function searchAttribute($label)
    {
        $this->getSession()->evaluateScript(
            sprintf(
                "jQuery('%s').val('%s').trigger('input');",
                $this->selectors['Available attributes search'],
                $label
            )
        );
    }

    /**
     * Find the Select2 result list for attributes
     *
     * @return mixed
     *
     * @throws TimeoutException
     */
    protected function findAttributeList()
    {
        $list = $this->spin(
            function () {
                return $this->find('css', $this->selectors['Available attributes list']);
            },
            sprintf('Cannot find the attribute list element "%s"', $this->selectors['Available attributes list'])
        );

        return $list;
    }
}
