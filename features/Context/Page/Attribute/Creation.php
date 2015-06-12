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
                'attribute_option_table' => array('css' => '#attribute-option-grid table'),
                'attribute_options'      => array('css' => '#attribute-option-grid tbody tr'),
                'add_option_button'      => array('css' => '#attribute-option-grid .btn.option-add'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findField($name)
    {
        $field = parent::findField($name);
        if (!$field) {
            $field = $this->getElement('attribute_option_table')->find('css', sprintf('th:contains("%s")', $name));
        }

        return $field;
    }

    /**
     * Add an attribute option
     *
     * @param string $name
     */
    public function addOption($name)
    {
        if (!$this->getElement('attribute_option_table')->find('css', '.attribute_option_code')) {
            $this->getElement('add_option_button')->click();
            $this->getSession()->wait(10000);
        }

        $rows = $this->getOptionsElement();
        $row  = end($rows);

        $row->find('css', '.attribute_option_code')->setValue($name);
        $row->find('css', '.btn.update-row')->click();
    }

    /**
     * Edit an attribute option
     *
     * @param string $name
     * @param string $newValue
     */
    public function editOption($name, $newValue)
    {
        $row = $this->getOptionElement($name);

        $row->find('css', '.edit-row')->click();
        $row->find('css', '.attribute_option_code')->setValue($newValue);
        $row->find('css', '.btn.update-row')->click();
    }

    /**
     * Edit and cancel edition on an attribute option
     *
     * @param string $name
     * @param string $newValue
     */
    public function editOptionAndCancel($name, $newValue)
    {
        $row = $this->getOptionElement($name);

        $row->find('css', '.edit-row')->click();
        $row->find('css', '.attribute_option_code')->setValue($newValue);
        $row->find('css', '.btn.show-row')->click();
    }

    /**
     * Count the number of attribute options
     *
     * @return int
     */
    public function countOptions()
    {
        return count($this->findAll('css', $this->elements['attribute_options']['css']));
    }

    /**
     * Count the number of attribute options
     *
     * @return int
     */
    public function countOrderableOptions()
    {
        return count($this->findAll('css', '#attribute-option-grid table:not(.ui-sortable-disabled) .handle'));
    }

    /**
     * Remove a specific option name
     *
     * @param string $optionName
     *
     * @throws \InvalidArgumentException
     */
    public function removeOption($optionName)
    {
        $optionRow = $this->getOptionElement($optionName);
        $deleteBtn = $optionRow->find('css', '.btn.delete-row');

        if ($deleteBtn === null) {
            throw new \InvalidArgumentException(
                sprintf('Remove bouton not found or disabled for %s option', $optionName)
            );
        }

        $deleteBtn->click();
    }

    /**
     * Get option elements
     *
     * @return array
     */
    protected function getOptionsElement()
    {
        return $this->findAll('css', $this->elements['attribute_options']['css']);
    }

    /**
     * Get a specific option row from the option code
     *
     * @param string $optionName
     *
     * @throws \InvalidArgumentException
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function getOptionElement($optionName)
    {
        foreach ($this->getOptionsElement() as $optionRow) {
            if ((
                    $optionRow->find('css', '.attribute_option_code') &&
                    $optionRow->find('css', '.attribute_option_code')->getValue() === $optionName
                ) ||
                (
                    $optionRow->find('css', '.option-code') &&
                    $optionRow->find('css', '.option-code')->getText() === $optionName
                )
            ) {
                return $optionRow;
            }
        }

        throw new \InvalidArgumentException(sprintf('Option %s was not found', $optionName));
    }
}
