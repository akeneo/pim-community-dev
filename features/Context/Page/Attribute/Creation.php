<?php

namespace Context\Page\Attribute;

use Context\Page\Base\Form;
use Context\Spin\TimeoutException;

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
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'attribute_option_table' => ['css' => '#attribute-option-grid table'],
                'attribute_options'      => ['css' => '#attribute-option-grid tbody tr'],
                'add_option_button'      => ['css' => '#attribute-option-grid .btn.option-add'],
                'new_option'             => ['css' => '.in-edition']
            ]
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
        try {
            $element = $this->spin(function () {
                return $this->getElement('attribute_option_table')->find('css', '.attribute_option_code');
            });
        } catch (TimeoutException $e) {
            $element = $this->spin(function () {
                return $this->getElement('add_option_button');
            }, 'Could not find the element');
        }

        $element->click();

        $rows = $this->getOptionsElement();
        $row  = end($rows);

        $attributeOption = $this->spin(function () use ($row) {
            return $row->find('css', '.attribute_option_code');
        }, 'Could not find the attribute option code');

        $attributeOption->setValue($name);

        $button = $this->spin(function () use ($row) {
            return $row->find('css', '.btn.update-row');
        }, 'Could not find button');

        $button->click();
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
        $this->spin(function () {
            $table = $this->find('css', $this->elements['attribute_option_table']['css']);

            if (null !== $table) {
                $this->spin(function () {
                    $row = $this->find('css', $this->elements['new_option']['css']);

                    return null === $row;
                });
            }

            return true;
        }, 'Attribute options are not visible');

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
        return $this->spin(function () {
            return $this->findAll('css', $this->elements['attribute_options']['css']);
        }, 'Could not find options element');
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
