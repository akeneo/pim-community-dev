<?php

namespace Context\Page\Family;

use Context\Page\Family\Creation;

/**
 * Family edit page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Creation
{
    protected $path = '/enrich/family/edit/{id}';

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
                'Attribute as label choices'      => array('css' => '#pim_family_form_attributeAsLabel'),
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
     * @param array $options
     *
     * @return string
     */
    public function getUrl(array $options = array())
    {
        $url = $this->getPath();

        foreach ($options as $parameter => $value) {
            $url = str_replace(sprintf('{%s}', $parameter), $value, $url);
        }

        return $url;
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

    public function isAttributeRequired($attribute, $channel)
    {
        $cell        = $this->getAttributeRequirementCell($attribute, $channel);
        $requirement = $cell->find('css', 'input');

        return '1' === $requirement->getValue();
    }

    public function switchAttributeRequirement($attribute, $channel)
    {
        $cell        = $this->getAttributeRequirementCell($attribute, $channel);
        $requirement = $cell->find('css', 'i');

        $requirement->click();
    }

    private function getAttributeRequirementCell($attribute, $channel)
    {
        $attributesTable = $this->getElement('Attributes');
        $columnIdx       = 0;

        foreach ($attributesTable->findAll('css', 'thead th') as $index => $header) {
            if ($header->getText() === $channel) {
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
