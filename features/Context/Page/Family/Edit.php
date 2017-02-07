<?php

namespace Context\Page\Family;

use Context\Page\Base\Form;
use Context\Spin\SpinCapableTrait;

/**
 * Family edit page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    use SpinCapableTrait;

    /**
     * @var string
     */
    protected $path = '/configuration/family/{code}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'Attributes'                 => ['css' => '.tab-pane.tab-attribute table'],
                'Attribute as label choices' => ['css' => '#pim_enrich_family_form_attributeAsLabel'],
                'Available attributes button'     => ['css' => '.add-attribute a.select2-choice'],
                'Available attributes'            => [
                    'css'        => '.add-attribute',
                    'decorators' => ['Pim\Behat\Decorator\Common\AddAttributeDecorator']
                ],
                'Available attributes list'       => ['css' => '.add-attribute .select2-results'],
                'Available attributes search'     => ['css' => '.add-attribute .select2-search input[type="text"]'],
                'Select2 dropmask'                => ['css' => '.select2-drop-mask'],
            ]
        );
    }

    /**
     * @param string $attributeName
     * @param string $groupName
     *
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     */
    public function getAttribute($attributeName, $groupName = null)
    {
        if (null !== $groupName) {
            return $this->getAttributeByGroupAndName($attributeName, $groupName);
        }

        return $this->getAttributeByName($attributeName);
    }

    /**
     * @param $attributeName
     *
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     */
    protected function getAttributeByName($attributeName)
    {
        $attributeNodes = $this->getElement('Attributes')->findAll('css', 'table.groups tbody tr:not(.group)');
        foreach ($attributeNodes as $attributeNode) {
            $attribute = $attributeNode->find('css', sprintf('td:contains("%s")', $attributeName));
            if (null !== $attribute) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @param $attribute
     * @param $group
     *
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     */
    protected function getAttributeByGroupAndName($attribute, $group)
    {
        $groupNode = $this->getElement('Attributes')->find('css', sprintf('tr.group:contains("%s")', $group));

        if (!$groupNode) {
            throw new \RuntimeException(
                sprintf(
                    'Couldn\'t find the attribute group "%s" in the attributes table',
                    $group
                )
            );
        }

        return $groupNode->getParent()->find('css', sprintf('td:contains("%s")', $attribute));
    }

    /**
     * @param string $attribute
     *
     * @return \Behat\Mink\Element\NodeElement|mixed
     */
    public function getRemoveLinkFor($attribute)
    {
        $attributeRow = $this->getElement('Attributes')->find('css', sprintf('tr:contains("%s")', $attribute));

        if (!$attributeRow) {
            throw new \RuntimeException(
                sprintf(
                    'Couldn\'t find the attribute row "%s" in the attributes table',
                    $attribute
                )
            );
        }

        $removeLink = $attributeRow->find('css', 'a.remove-attribute');

        if (!$removeLink) {
            throw new \RuntimeException(
                sprintf(
                    'Couldn\'t find the attribute remove link for "%s" in the attributes table',
                    $attribute
                )
            );
        }

        return $removeLink;
    }

    /**
     * @return array
     */
    public function getAttributeAsLabelOptions()
    {
        $options = array_map(
            function ($option) {
                return $option->getText();
            },
            $this->getElement('Attribute as label choices')->findAll('css', 'option')
        );
        $options[0] = $this->find('css', '#s2id_pim_enrich_family_form_attributeAsLabel .select2-chosen')->getText();

        return $options;
    }

    /**
     * @param string $attributeCode
     * @param string $channelCode
     *
     * @return bool
     */
    public function isAttributeRequired($attributeCode, $channelCode)
    {
        $selector = '.attribute-requirement [data-channel="%s"][data-attribute="%s"]';
        $checkbox = $this->find('css', sprintf($selector, $channelCode, $attributeCode));
        if (!$checkbox) {
            throw new \RuntimeException(
                sprintf(
                    'Couldn\'t find "%s" attribute requirement for channel "%s"',
                    $attributeCode,
                    $channelCode
                )
            );
        }

        return $checkbox->isChecked();
    }

    /**
     * @param string $attribute
     * @param string $channel
     */
    public function switchAttributeRequirement($attribute, $channel)
    {
        $cell        = $this->getAttributeRequirementCell($attribute, $channel);
        $requirement = $cell->find('css', 'i');

        $loadingMask = $this->find('css', '.hash-loading-mask .loading-mask');

        $this->spin(function () use ($loadingMask) {
            return (null === $loadingMask) || !$loadingMask->isVisible();
        }, '".loading-mask" is still visible');

        $requirement->click();
    }

    /**
     * @param string $attribute
     * @param string $channel
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    protected function getAttributeRequirementCell($attribute, $channel)
    {
        $columnIdx = $this->spin(function () use ($channel) {
            $attributesTable = $this->getElement('Attributes');
            foreach ($attributesTable->findAll('css', 'thead th') as $index => $header) {
                if ($header->getText() === strtoupper($channel)) {
                    return $index;
                }
            }

            return false;
        }, "You're fired");

        $attributesTable = $this->getElement('Attributes');
        $cells = $attributesTable->findAll('css', sprintf('tbody tr:contains("%s") td', $attribute));

        if (count($cells) < $columnIdx) {
            throw new \Exception(sprintf('An error occured when trying to get the attributes "%s" row', $attribute));
        }

        return $cells[$columnIdx];
    }

    /**
     * @param string $attribute
     * @param string $group
     *
     * @return NodeElement|null
     */
    public function findAvailableAttributeInGroup($attribute, $group)
    {
        $searchSelector = $this->elements['Available attributes search']['css'];

        $selector = $this->spin(function () {
            return $this->find('css', $this->elements['Available attributes button']['css']);
        }, sprintf('Cannot find element "%s"', $this->elements['Available attributes button']['css']));

        // Open select2
        $selector->click();

        $list = $this->spin(function () {
            return $this->getElement('Available attributes list');
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
        $this->find('css', '#select2-drop-mask')->click();

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
        $availableAttribute = $this->spin(function () {
            return $this->getElement('Available attributes');
        }, 'Cannot find the add attribute element');
        $availableAttribute->addAttributes($attributes);
    }
}
