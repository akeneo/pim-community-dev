<?php

namespace Context\Page\Family;

use Context\Page\Base\Form;

/**
 * Family edit page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /**
     * @var string
     */
    protected $path = '/configuration/family/{id}/edit';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Attributes'                 => array('css' => '.tab-pane.tab-attribute table'),
                'Attribute as label choices' => array('css' => '#pim_enrich_family_form_attributeAsLabel'),
            )
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
        $selector = '#pim_enrich_family_form_indexedAttributeRequirements_%s_%s_required';
        $checkbox = $this->find('css', sprintf($selector, $attributeCode, $channelCode));
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
        $attributesTable = $this->getElement('Attributes');
        $columnIdx       = 0;

        foreach ($attributesTable->findAll('css', 'thead th') as $index => $header) {
            if ($header->getText() === strtoupper($channel)) {
                $columnIdx = $index;
                break;
            }
        }

        if (0 === $columnIdx) {
            throw new \Exception(sprintf('An error occured when trying to get the "%s" header', $channel));
        }

        $cells = $attributesTable->findAll('css', sprintf('tbody tr:contains("%s") td', $attribute));

        if (count($cells) < $columnIdx) {
            throw new \Exception(sprintf('An error occured when trying to get the attributes "%s" row', $attribute));
        }

        return $cells[$columnIdx];
    }
}
