<?php

namespace Context\Page\Attribute;

use Context\Page\Base\Form;

/**
 * Attribute creation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /**
     * @var string
     */
    protected $path = '/configuration/attribute/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            array(
                'Attribute options table' => array('css' => 'table#sortable_options'),
                'Attribute options'       => array('css' => 'table#sortable_options tbody tr'),
                'Add option button'       => array('css' => 'a.btn.add_option_link'),
                'Default label field'     => array('css' => '#pim_catalog_attribute_form_label_default'),
                'Updates grid'            => array('css' => '#history table.grid'),
            )
        );
    }

    /**
     * @return array
     */
    public function getHistoryRows()
    {
        return $this->getElement('Updates grid')->findAll('css', 'tbody tr');
    }

    /**
     * {@inheritdoc}
     */
    public function findField($name)
    {
        $field = parent::findField($name);
        if (!$field) {
            $field = $this->getElement('Attribute options table')->find('css', sprintf('th:contains("%s")', $name));
        }

        return $field;
    }

    /**
     * Add an attribute option
     * @param string  $name
     * @param boolean $selectedByDefault
     */
    public function addOption($name, $selectedByDefault = 'no')
    {
        $selectedByDefault = strtolower($selectedByDefault == 'yes') ? true : false;

        $rows = $this->findAll('css', $this->elements['Attribute options']['css']);

        foreach ($rows as $row) {
            if (!$row->find('css', '[id*="_code"]')->getValue()) {
                $row->find('css', '[id*="_code"]')->setValue($name);
                if ($selectedByDefault) {
                    $row->find('css', 'input[name="default"]')->click();
                }

                return;
            }
        }

        $this->getElement('Add option button')->click();

        $rows = $this->findAll('css', $this->elements['Attribute options']['css']);
        $row = end($rows);

        $row->find('css', '[id*="_code"]')->setValue($name);
        if ($selectedByDefault) {
            $row->find('css', 'input[name="default"]')->click();
        }
    }

    /**
     * Count the number of attribute options
     * @return integer
     */
    public function countOptions()
    {
        return count($this->findAll('css', $this->elements['Attribute options']['css']));
    }

    /**
     * Count the number of removable attribute options
     * @return integer
     */
    public function countRemovableOptions()
    {
        return count($this->findAll('css', 'button.action-delete-inline:not([disabled])'));
    }

    /**
     * Fill the default label field
     * @param string $value
     */
    public function fillDefaultLabelField($value)
    {
        $this->getElement('Default label field')->setValue($value);
    }
}
