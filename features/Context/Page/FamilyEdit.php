<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyEdit extends Page
{
    protected $path = '/enrich/family/edit/{family_id}';

    protected $elements = array(
        'Available attributes'            => array('css' => '#pim_available_product_attributes_attributes'),
        'Available attributes menu'       => array('css' => 'button:contains("Add attributes")'),
        'Available attributes add button' => array('css' => 'a:contains("Add")'),
        'Attributes'                      => array('css' => '#attributes table'),
        'Tabs'                            => array('css' => '#form-navbar'),
        'Attribute as label choices'      => array('css' => '#pim_family_form_attributeAsLabel'),
    );

    public function getAvailableAttribute($attribute, $group)
    {
        return $this
            ->getElement('Available attributes')
            ->find('css', sprintf(
                'optgroup[label="%s"] option:contains("%s")',
                $group, $attribute
            ))
        ;
    }

    public function getAttribute($attribute, $group)
    {
        $groupNode = $this
            ->getElement('Attributes')
            ->find('css', sprintf(
                'tr.group:contains("%s")', $group
            ));

        if (!$groupNode) {
            throw new \RuntimeException(sprintf(
                'Couldn\'t find the attribute group "%s" in the attributes table',
                $group
            ));
        }

        return $groupNode
            ->getParent()
            ->find('css', sprintf(
                'td:contains("%s")', $attribute
            ))
        ;
    }

    public function selectAvailableAttribute($attribute)
    {
        $this->getElement('Available attributes')->selectOption($attribute, true);
    }

    public function addSelectedAvailableAttributes()
    {
        $this
            ->getElement('Available attributes add button')
            ->press()
        ;
    }

    public function save()
    {
        $this->pressButton('Save');
    }

    public function getUrl(array $options)
    {
        $url = $this->getPath();

        foreach ($options as $parameter => $value) {
            $url = str_replace(sprintf('{%s}', $parameter), $value, $url);
        }

        return $url;
    }

    public function getFieldLocator($name, $locale)
    {
        return sprintf('pim_family_form_%s_%s', strtolower($name), $locale);
    }

    public function getRemoveLinkFor($attribute)
    {
        $attributeRow = $this
            ->getElement('Attributes')
            ->find('css', sprintf(
                'tr:contains("%s")', $attribute
            ));

        if (!$attributeRow) {
            throw new \RuntimeException(sprintf(
                'Couldn\'t find the attribute row "%s" in the attributes table',
                $attribute
            ));
        }

        $removeLink = $attributeRow->find('css', 'a.remove-attribute');

        if (!$removeLink) {
            throw new \RuntimeException(sprintf(
                'Couldn\'t find the attribute remove link for "%s" in the attributes table',
                $attribute
            ));
        }

        return $removeLink;
    }

    public function visitTab($tab)
    {
        $this->getElement('Tabs')->clickLink($tab);
    }

    public function getAttributeAsLabelOptions()
    {
        return array_map(function ($option) {
            return $option->getText();
        }, $this->getElement('Attribute as label choices')->findAll('css', 'option'));
    }

    public function selectAttributeAsLabel($attribute)
    {
        $this->getElement('Attribute as label choices')->selectOption($attribute);

        return $this;
    }

    public function openAvailableAttributesMenu()
    {
        $this->visitTab('Attributes');
        $this->getElement('Available attributes menu')->click();
    }
}
