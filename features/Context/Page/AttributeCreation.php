<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCreation extends Page
{
    protected $path = '/enrich/product-attribute/create';

    protected $elements = array(
        'Attribute type selector' => array('css' => '#pim_product_attribute_form_attributeType'),
        'Attribute options table' => array('css' => 'table#sortable_options'),
        'Attribute options'       => array('css' => 'table#sortable_options tbody tr'),
        'Add option button'       => array('css' => 'a.btn.add_option_link'),
        'Tabs'                    => array('css' => '#form-navbar'),
        'Default label field'     => array('css' => '#pim_product_attribute_form_label_default'),
    );

    public function selectAttributeType($type)
    {
        $this
            ->getElement('Attribute type selector')
            ->selectOption($type)
        ;
    }

    public function findField($name)
    {
        $field = parent::findField($name);
        if (!$field) {
            $field = $this->getElement('Attribute options table')->find('css', sprintf('th:contains("%s")', $name));
        }

        return $field;
    }

    public function addOption($name, $selectedByDefault = 'no')
    {
        $selectedByDefault = strtolower($selectedByDefault == 'yes') ? true : false;

        $rows = $this->findAll('css', $this->elements['Attribute options']['css']);

        foreach ($rows as $key => $row) {
            if (!$row->find('css', '[id*="_defaultValue"]')->getValue()) {
                $row->find('css', '[id*="_defaultValue"]')->setValue($name);
                if ($selectedByDefault) {
                    $row->find('css', 'input[name="default"]')->click();
                }
                return;
            }
        }

        $this->getElement('Add option button')->click();

        $rows = $this->findAll('css', $this->elements['Attribute options']['css']);
        $row = end($rows);

        $row->find('css', '[id*="_defaultValue"]')->setValue($name);
        if ($selectedByDefault) {
            $row->find('css', 'input[name="default"]')->click();
        }
    }

    public function save()
    {
        $this->pressButton('Save');
    }

    public function getSection($title)
    {
        return $this->find('css', sprintf('div.accordion-heading:contains("%s")', $title));
    }

    public function countOptions()
    {
        return count($this->findAll('css', $this->elements['Attribute options']['css']));
    }

    public function countRemovableOptions()
    {
        return count($this->findAll('css', 'button.action-delete-inline:not([disabled])'));
    }

    public function fillDefaultLabelField($value)
    {
        $this->getElement('Default label field')->setValue($value);
    }

    public function visitTab($tab)
    {
        $this->getElement('Tabs')->clickLink($tab);
    }
}
