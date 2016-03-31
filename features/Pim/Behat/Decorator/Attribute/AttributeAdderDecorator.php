<?php

namespace Pim\Behat\Decorator\Attribute;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
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
            sprintf("jQuery('%s').click();", $this->selectors['Select2 dropmask']['css'])
        );

        return isset($results[$label]) ? $results[$label] : null;
    }

    /**
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
                sprintf('Could not find available attribute "%s" (%s)', $label, $attributeCssSelector)
            );

            $label->click();
        }

        $this->find('css', $this->selectors['Available attributes add button']['css'])->press();
    }

    /**
     * @return Element
     *
     * @throws TimeoutException
     */
    protected function openSelector()
    {
        $selector = $this->spin(
            function () {
                return $this->find('css', $this->selectors['Available attributes button']['css']);
            },
            sprintf('Cannot find the attribute selector "%s"', $this->selectors['Available attributes button']['css'])
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
                $this->selectors['Available attributes search']['css'],
                $label
            )
        );
    }

    /**
     * @return mixed
     *
     * @throws TimeoutException
     */
    protected function findAttributeList()
    {
        $list = $this->spin(
            function () {
                return $this->find('css', $this->selectors['Available attributes list']['css']);
            },
            sprintf('Cannot find the attribute list element "%s"', $this->selectors['Available attributes list']['css'])
        );

        return $list;
    }
}
