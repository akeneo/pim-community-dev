<?php

namespace Context\Page\Family;

use Context\Page\Family\Creation;

/**
 * Family edit page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Creation
{
    /**
     * @var string $path
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
                'Attributes'                      => array('css' => '#attributes table'),
                'Attribute as label choices'      => array('css' => '#pim_family_attributeAsLabel'),
                'Updates grid'                    => array('css' => '#history table.grid'),
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $group
     *
     * @return NodeElement
     */
    public function getAttribute($attribute, $group)
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
     * @return NodeElement
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
        return array_map(
            function ($option) {
                return $option->getText();
            },
            $this->getElement('Attribute as label choices')->findAll('css', 'option')
        );
    }

    /**
     * @param string $attribute
     *
     * @return Edit
     */
    public function selectAttributeAsLabel($attribute)
    {
        $this->getElement('Attribute as label choices')->selectOption($attribute);

        return $this;
    }

    /**
     * @param string $attributeCode
     * @param string $channelCode
     *
     * @return boolean
     */
    public function isAttributeRequired($attributeCode, $channelCode)
    {
        $selector = '#pim_family_attributeRequirements_%s_%s_required';
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
     * @return NodeElement
     */
    private function getAttributeRequirementCell($attribute, $channel)
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

    /**
     * @return array
     */
    public function getHistoryRows()
    {
        return $this->getElement('Updates grid')->findAll('css', 'tbody tr');
    }
}
